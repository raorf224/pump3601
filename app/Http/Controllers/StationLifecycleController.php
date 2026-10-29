<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StationLifecycleController extends Controller
{
    public function reset(Request $request, int $stationId): JsonResponse
    {
        return $this->run($request, $stationId, false);
    }

    public function destroy(Request $request, int $stationId): JsonResponse
    {
        return $this->run($request, $stationId, true);
    }

    private function run(Request $request, int $stationId, bool $deleteStation): JsonResponse
    {
        $this->ensureAdmin($request);
        DB::beginTransaction();

        try {
            $station = DB::table('stations')->where('id', $stationId)->lockForUpdate()->first();
            if (!$station) {
                DB::rollBack();
                return response()->json(['message' => 'Site not found.'], 404);
            }

            $summary = $this->purgeStationData($stationId, $deleteStation, (int) $station->user_id);

            if ($deleteStation) {
                DB::table('stations')->where('id', $stationId)->delete();
            } else {
                // A reset must never unassign the site's regional/territory managers.
                // Restore the locked station values in case any cleanup-side FK/trigger
                // cleared them while operational data was being removed.
                DB::table('stations')->where('id', $stationId)->update([
                    'rm_user_id' => $station->rm_user_id,
                    'tm_user_id' => $station->tm_user_id,
                ]);
            }

            DB::commit();

            Log::warning($deleteStation ? 'Site permanently deleted' : 'Site data reset', [
                'station_id' => $stationId,
                'performed_by' => $request->user()->id,
                'summary' => $summary,
            ]);

            return response()->json([
                'message' => $deleteStation
                    ? 'Site, employees and all related data deleted successfully.'
                    : 'Site data reset successfully. Site and employees were preserved.',
                'summary' => $summary,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Site lifecycle operation failed', [
                'station_id' => $stationId,
                'operation' => $deleteStation ? 'delete' : 'reset',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Operation failed. No site data was changed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function purgeStationData(int $stationId, bool $deleteStation, int $ownerUserId): array
    {
        $employeeRows = DB::table('employees')->where('station_id', $stationId)->lockForUpdate()->get();
        $employeeIds = $employeeRows->pluck('id');
        $employeeUserIds = $employeeRows->pluck('user_id')->filter()->unique();
        $shiftIds = DB::table('shifts')->where('station_id', $stationId)->pluck('id');
        $tankIds = DB::table('tanks')->where('station_id', $stationId)->pluck('id');
        $dispenserIds = DB::table('dispensers')->where('station_id', $stationId)->pluck('id');
        $nozzleIds = DB::table('nozzles')
            ->where('station_id', $stationId)
            ->orWhereIn('tank_id', $tankIds)
            ->orWhereIn('dispenser_id', $dispenserIds)
            ->pluck('id');
        $readingIds = DB::table('shift_nozzle_readings')
            ->whereIn('shift_id', $shiftIds)
            ->orWhereIn('nozzle_id', $nozzleIds)
            ->pluck('id');
        $stationProductIds = DB::table('station_products')->where('station_id', $stationId)->pluck('id');
        $oilPurchaseIds = DB::table('oil_purchase')->where('station_id', $stationId)->pluck('id');
        $lubeDocumentIds = DB::table('lube_documents')->where('station_id', $stationId)->pluck('id');
        $lubeInventoryIds = DB::table('lube_inventory')->where('station_id', $stationId)->pluck('id');

        $summary = [
            'shifts' => $shiftIds->count(),
            'tanks' => $tankIds->count(),
            'nozzles' => $nozzleIds->count(),
            'employees' => $deleteStation ? $employeeIds->count() : 0,
        ];

        if ($readingIds->isNotEmpty()) {
            DB::table('fuel_layer_consumptions')->whereIn('sale_id', $readingIds)->delete();
        }
        if ($tankIds->isNotEmpty()) {
            DB::table('fuel_inventory_layers')->whereIn('tank_id', $tankIds)->delete();
            DB::table('tanks_dip')->whereIn('tank_id', $tankIds)->delete();
            DB::table('tank_dispenser')->whereIn('tank_id', $tankIds)->delete();
        }
        if ($nozzleIds->isNotEmpty()) {
            DB::table('nozzle_totalizer_resets')->whereIn('nozzle_id', $nozzleIds)->delete();
            DB::table('shift_nozzle_readings')->whereIn('nozzle_id', $nozzleIds)->delete();
        }
        if ($dispenserIds->isNotEmpty()) {
            DB::table('tank_dispenser')->whereIn('dispenser_id', $dispenserIds)->delete();
        }
        if ($stationProductIds->isNotEmpty()) {
            DB::table('product_prices')->whereIn('station_product_id', $stationProductIds)->delete();
        }
        if ($oilPurchaseIds->isNotEmpty()) {
            DB::table('shortage_ammount_paid_back')->whereIn('oil_purchase_id', $oilPurchaseIds)->delete();
            DB::table('ammount_paid')->whereIn('oil_purchase_id', $oilPurchaseIds)->delete();
            DB::table('oil_recived_tanks')->whereIn('oil_purchase_id', $oilPurchaseIds)->delete();
        }
        if ($lubeDocumentIds->isNotEmpty()) {
            DB::table('ammount_paid')->whereIn('lube_id', $lubeDocumentIds)->delete();
            DB::table('lube_inventory_logs')->whereIn('document_id', $lubeDocumentIds)->delete();
            DB::table('lube_lines')->whereIn('document_id', $lubeDocumentIds)->delete();
        }
        if ($lubeInventoryIds->isNotEmpty()) {
            DB::table('lube_inventory_logs')->whereIn('inventory_id', $lubeInventoryIds)->delete();
        }

        if ($shiftIds->isNotEmpty()) {
            foreach ([
                'attendance', 'credit_driver', 'nozzle_totalizer_resets', 'oil_recived_tanks',
                'product_prices', 'shift_cash_flow', 'shift_nozzle_readings',
                'shortage_ammount_paid_back', 'tanks_dip', 'transactions', 'ammount_paid',
            ] as $table) {
                if ($this->hasColumn($table, 'shift_id')) {
                    DB::table($table)->whereIn('shift_id', $shiftIds)->delete();
                }
            }
        }

        // Remove parent setup rows only after all of their child/history rows are gone.
        DB::table('lube_documents')->where('station_id', $stationId)->delete();
        DB::table('lube_inventory')->where('station_id', $stationId)->delete();
        DB::table('nozzles')->where('station_id', $stationId)->delete();
        DB::table('tank_dispenser')->where('station_id', $stationId)->delete();
        DB::table('tanks')->where('station_id', $stationId)->delete();
        DB::table('dispensers')->where('station_id', $stationId)->delete();
        DB::table('product_prices')->where('station_id', $stationId)->delete();
        DB::table('station_products')->where('station_id', $stationId)->delete();
        DB::table('oil_purchase')->where('station_id', $stationId)->delete();
        DB::table('shifts')->where('station_id', $stationId)->delete();
        DB::table('products')->where('station_id', $stationId)->delete();
        DB::table('accounts')->where('station_id', $stationId)->delete();

        // Delete every remaining station-owned data row. Master site/employee/user rows are handled separately.
        $stationTables = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('COLUMN_NAME', 'station_id')
            ->whereNotIn('TABLE_NAME', ['stations', 'employees', 'users'])
            ->pluck('TABLE_NAME')
            ->unique();

        foreach ($stationTables as $table) {
            DB::table($table)->where('station_id', $stationId)->delete();
        }

        if ($deleteStation) {
            if ($employeeIds->isNotEmpty()) {
                DB::table('attendance')->whereIn('employee_id', $employeeIds)->delete();
                DB::table('payroll')->whereIn('employee_id', $employeeIds)->delete();
                DB::table('employees')->whereIn('id', $employeeIds)->delete();
            }

            foreach ($employeeUserIds as $userId) {
                $stillEmployee = DB::table('employees')->where('user_id', $userId)->exists();
                if (!$stillEmployee && (int) $userId !== $ownerUserId) {
                    DB::table('users')->where('id', $userId)->delete();
                }
            }
        }

        return $summary;
    }

    private function hasColumn(string $table, string $column): bool
    {
        return DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->exists();
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user() && $request->user()->role === 'admin', 403, 'Only admin can reset or delete sites.');
    }
}
