<?php

namespace App\Http\Controllers;

use App\Support\StationAccess;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MainDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'station_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
        ]);

        $fromDate = Carbon::parse($validated['from_date'] ?? now()->subDays(6)->toDateString())->startOfDay();
        $toDate = Carbon::parse($validated['to_date'] ?? now()->toDateString())->endOfDay();
        $accessibleStations = StationAccess::query($request->user())
            ->select('stations.id', 'stations.name')
            ->orderBy('stations.name')
            ->get();

        $accessibleStationIds = $accessibleStations->pluck('id')->map(fn ($id) => (int) $id)->all();
        $stationIds = $accessibleStationIds;

        if (!empty($validated['station_id'])) {
            $requestedStationId = (int) $validated['station_id'];
            abort_unless(in_array($requestedStationId, $accessibleStationIds, true), 403, 'You cannot access this station.');
            $stationIds = [$requestedStationId];
        }

        $productId = !empty($validated['product_id']) ? (int) $validated['product_id'] : null;
        $products = $this->accessibleProducts($accessibleStationIds);
        if ($productId && !$products->contains('id', $productId)) {
            abort(422, 'The selected product is not available at your stations.');
        }

        if (!$stationIds) {
            return response()->json($this->emptyResponse($fromDate, $toDate, $accessibleStations, $products));
        }

        $includeAllAdminShifts = $request->user()->role === 'admin' && empty($validated['station_id']);

        $shifts = DB::table('shifts')
            ->when(!$includeAllAdminShifts, fn ($query) => $query->whereIn('station_id', $stationIds))
            ->where('start_time', '>=', $fromDate)
            ->where('end_time', '<=', $toDate)
            ->select('id', 'station_id', 'shift_no', 'start_time', 'end_time', 'status')
            ->orderBy('start_time')
            ->get();

        $shiftIds = $shifts->pluck('id')->map(fn ($id) => (int) $id)->all();
        $stationNames = $accessibleStations->pluck('name', 'id');

        $nozzleReadings = $this->nozzleReadings($shiftIds, $productId);
        $nozzleResets = $this->nozzleResets($shiftIds, $productId);
        $cashFlows = $this->latestCashFlows($shiftIds);
        $transactions = $this->transactions($shiftIds, $productId);
        $partyCredits = $this->partyCredits($shiftIds, $productId);
        $tankVariances = $this->tankVariances($shiftIds, $stationIds, $productId);

        $fuelSales = (float) ($nozzleReadings->sum('total_amount') + $nozzleResets->sum('total_amount'));
        $litresSold = (float) ($nozzleReadings->sum('total_dispensed') + $nozzleResets->sum('total_dispensed'));
        $cashCollected = (float) $cashFlows->sum(fn ($row) =>
            (float) $row->in_hand + (float) $row->in_bank + (float) $row->fuelcard + (float) $row->creditcard
        );
        $income = (float) $transactions->where('type', 'income')->sum('credit');
        $expenses = (float) $transactions->where('type', 'expense')->sum('debit');

        return response()->json([
            'success' => true,
            'meta' => [
                'from_date' => $fromDate->toDateString(),
                'to_date' => $toDate->toDateString(),
                'generated_at' => now()->toIso8601String(),
            ],
            'filters' => [
                'stations' => $accessibleStations,
                'products' => $products,
            ],
            'summary' => [
                'fuel_sales' => $fuelSales,
                'litres_sold' => $litresSold,
                'shift_count' => $shifts->count(),
                'cash_collected' => $cashCollected,
                'active_nozzles' => $nozzleReadings->pluck('nozzle_id')
                    ->merge($nozzleResets->pluck('nozzle_id'))
                    ->unique()
                    ->count(),
                'party_credit' => (float) $partyCredits->sum('amount'),
                'stock_loss' => abs((float) $tankVariances->where('variance_litres', '<', 0)->sum('variance_value')),
                'stock_gain' => (float) $tankVariances->where('variance_litres', '>', 0)->sum('variance_value'),
                'income' => $income,
                'expenses' => $expenses,
                'net_income' => $income - $expenses,
            ],
            'product_summary' => $this->productSummary($nozzleReadings, $nozzleResets, $products),
            'sales_trend' => $this->salesTrend($nozzleReadings, $nozzleResets),
            'nozzle_sales' => $this->nozzleSales($nozzleReadings, $nozzleResets),
            'station_performance' => $this->stationPerformance($nozzleReadings, $nozzleResets, $stationNames),
            'shift_activity' => $this->shiftActivity($shifts, $nozzleReadings, $nozzleResets, $stationNames),
            'cash_distribution' => [
                'cash' => (float) $cashFlows->sum('in_hand'),
                'bank' => (float) $cashFlows->sum('in_bank'),
                'fuel_card' => (float) $cashFlows->sum('fuelcard'),
                'credit_card' => (float) $cashFlows->sum('creditcard'),
            ],
            'tank_levels' => $this->tankLevels($shiftIds, $stationIds, $productId),
            'tank_variances' => $tankVariances->values(),
            'station_finance' => $this->stationFinance($stationIds, $stationNames, $transactions, $partyCredits, $tankVariances),
            'records' => $this->records($shifts, $nozzleReadings, $transactions, $partyCredits, $tankVariances, $stationNames),
        ]);
    }

    private function accessibleProducts(array $stationIds): Collection
    {
        if (!$stationIds) {
            return collect();
        }

        return DB::table('station_products as sp')
            ->join('products as p', 'p.id', '=', 'sp.product_id')
            ->whereIn('sp.station_id', $stationIds)
            ->where('p.category', 'fuel')
            ->select('p.id', 'p.name')
            ->distinct()
            ->orderBy('p.name')
            ->get();
    }

    private function nozzleReadings(array $shiftIds, ?int $productId): Collection
    {
        if (!$shiftIds) return collect();

        return DB::table('shift_nozzle_readings as reading')
            ->join('shifts as shift', 'shift.id', '=', 'reading.shift_id')
            ->leftJoin('stations as station', 'station.id', '=', 'shift.station_id')
            ->leftJoin('nozzles as nozzle', 'nozzle.id', '=', 'reading.nozzle_id')
            ->leftJoin('dispensers as dispenser', 'dispenser.id', '=', 'nozzle.dispenser_id')
            ->leftJoin('products as product', 'product.id', '=', 'nozzle.product_id')
            ->whereIn('reading.shift_id', $shiftIds)
            ->when($productId, fn ($query) => $query->where('nozzle.product_id', $productId))
            ->select(
                'reading.id', 'reading.shift_id', 'reading.nozzle_id', 'reading.opening_reading',
                'reading.closing_reading', 'reading.total_dispensed', 'reading.rate', 'reading.total_amount',
                'shift.station_id', 'shift.shift_no', 'shift.start_time', 'station.name as station_name',
                'nozzle.name as nozzle_name', 'nozzle.tank_id', 'nozzle.product_id',
                'dispenser.name as dispenser_name', 'product.name as product_name'
            )
            ->get();
    }

    private function nozzleResets(array $shiftIds, ?int $productId): Collection
    {
        if (!$shiftIds) return collect();

        return DB::table('nozzle_totalizer_resets as reset')
            ->join('shifts as shift', 'shift.id', '=', 'reset.shift_id')
            ->leftJoin('stations as station', 'station.id', '=', 'shift.station_id')
            ->leftJoin('nozzles as nozzle', 'nozzle.id', '=', 'reset.nozzle_id')
            ->leftJoin('dispensers as dispenser', 'dispenser.id', '=', 'nozzle.dispenser_id')
            ->leftJoin('products as product', 'product.id', '=', 'nozzle.product_id')
            ->whereIn('reset.shift_id', $shiftIds)
            ->when($productId, fn ($query) => $query->where('nozzle.product_id', $productId))
            ->select(
                'reset.id', 'reset.shift_id', 'reset.nozzle_id', 'reset.total_dispensed',
                'reset.rate', 'reset.total_amount', 'reset.reset_date', 'shift.station_id', 'station.name as station_name',
                'shift.shift_no', 'shift.start_time', 'nozzle.name as nozzle_name', 'nozzle.tank_id',
                'nozzle.product_id', 'dispenser.name as dispenser_name', 'product.name as product_name'
            )
            ->get();
    }

    private function latestCashFlows(array $shiftIds): Collection
    {
        if (!$shiftIds) return collect();

        $latestIds = DB::table('shift_cash_flow')
            ->whereIn('shift_id', $shiftIds)
            ->selectRaw('MAX(id)')
            ->groupBy('shift_id');

        return DB::table('shift_cash_flow')
            ->whereIn('id', $latestIds)
            ->get();
    }

    private function transactions(array $shiftIds, ?int $productId): Collection
    {
        if (!$shiftIds || $productId) return collect();

        return DB::table('transactions as transaction')
            ->join('shifts as shift', 'shift.id', '=', 'transaction.shift_id')
            ->leftJoin('accounts as account', 'account.id', '=', 'transaction.account_id')
            ->whereIn('transaction.shift_id', $shiftIds)
            ->where(function ($query) {
                $query->whereNull('transaction.is_testing')->orWhere('transaction.is_testing', 0);
            })
            ->select(
                'transaction.id', 'transaction.shift_id', 'transaction.station_id', 'transaction.type',
                'transaction.debit', 'transaction.credit', 'transaction.method', 'transaction.note',
                'transaction.created_at', 'account.name as account_name', 'account.type as account_type',
                'shift.shift_no'
            )
            ->get();
    }

    private function partyCredits(array $shiftIds, ?int $productId): Collection
    {
        if (!$shiftIds || $productId) return collect();

        return DB::table('credit_driver as credit')
            ->join('shifts as shift', 'shift.id', '=', 'credit.shift_id')
            ->leftJoin('accounts as account', 'account.id', '=', 'credit.account_id')
            ->whereIn('credit.shift_id', $shiftIds)
            ->where('credit.is_paid', 0)
            ->select(
                'credit.id', 'credit.shift_id', 'credit.station_id', 'credit.account_id',
                'credit.amount_given_to', 'credit.vehicle_number', 'credit.amount', 'credit.created_at',
                'account.name as account_name', 'shift.shift_no'
            )
            ->get();
    }

    private function activeNozzleCount(array $stationIds, ?int $productId): int
    {
        return DB::table('nozzles')
            ->whereIn('station_id', $stationIds)
            ->where('status', 1)
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->count();
    }

    private function productSummary(Collection $readings, Collection $resets, Collection $products): array
    {
        $readingsByProduct = $readings->groupBy('product_id');
        $resetsByProduct = $resets->groupBy('product_id');

        $summary = $products->map(function ($product) use ($readingsByProduct, $resetsByProduct) {
            $productReadings = $readingsByProduct->get($product->id, collect());
            $productResets = $resetsByProduct->get($product->id, collect());
            return [
                'product_id' => (int) $product->id,
                'product_name' => $product->name,
                'litres' => (float) ($productReadings->sum('total_dispensed') + $productResets->sum('total_dispensed')),
                'amount' => (float) ($productReadings->sum('total_amount') + $productResets->sum('total_amount')),
                'nozzle_count' => $productReadings->pluck('nozzle_id')->unique()->count(),
            ];
        })->filter(fn ($row) => $row['litres'] > 0 || $row['amount'] > 0)->values();

        $unknownReadings = $readingsByProduct->get('', $readingsByProduct->get(null, collect()));
        $unknownResets = $resetsByProduct->get('', $resetsByProduct->get(null, collect()));
        if ($unknownReadings->isNotEmpty() || $unknownResets->isNotEmpty()) {
            $summary->push([
                'product_id' => 0,
                'product_name' => 'Unknown product',
                'litres' => (float) ($unknownReadings->sum('total_dispensed') + $unknownResets->sum('total_dispensed')),
                'amount' => (float) ($unknownReadings->sum('total_amount') + $unknownResets->sum('total_amount')),
                'nozzle_count' => $unknownReadings->pluck('nozzle_id')->merge($unknownResets->pluck('nozzle_id'))->unique()->count(),
            ]);
        }

        return $summary->values()->all();
    }

    private function salesTrend(Collection $readings, Collection $resets): array
    {
        return $readings->concat($resets)->groupBy(function ($row) {
            return Carbon::parse($row->start_time)->toDateString().'|'.$row->product_id;
        })->map(function ($rows) {
            $first = $rows->first();
            return [
                'date' => Carbon::parse($first->start_time)->toDateString(),
                'product_id' => (int) $first->product_id,
                'product_name' => $first->product_name ?? 'Unknown product',
                'litres' => (float) $rows->sum('total_dispensed'),
                'amount' => (float) $rows->sum('total_amount'),
            ];
        })->sortBy('date')->values()->all();
    }

    private function nozzleSales(Collection $readings, Collection $resets): array
    {
        return $readings->concat($resets)->groupBy('nozzle_id')->map(function ($rows) {
            $first = $rows->first();
            return [
                'nozzle_id' => (int) $first->nozzle_id,
                'label' => trim(($first->station_name ?? 'Unknown station').' '.($first->dispenser_name ?? 'Unknown dispenser').' '.($first->nozzle_name ?? ('Nozzle #'.$first->nozzle_id))),
                'station_name' => $first->station_name ?? 'Unknown station',
                'product_name' => $first->product_name ?? 'Unknown product',
                'litres' => (float) $rows->sum('total_dispensed'),
                'amount' => (float) $rows->sum('total_amount'),
            ];
        })->sortByDesc('litres')->values()->all();
    }

    private function stationPerformance(Collection $readings, Collection $resets, Collection $stationNames): array
    {
        return $readings->concat($resets)->groupBy('station_id')->map(function ($rows, $stationId) use ($stationNames) {
            return [
                'station_id' => (int) $stationId,
                'station_name' => $stationNames[$stationId] ?? 'Unknown station',
                'litres' => (float) $rows->sum('total_dispensed'),
                'amount' => (float) $rows->sum('total_amount'),
            ];
        })->sortByDesc('litres')->values()->all();
    }

    private function shiftActivity(Collection $shifts, Collection $readings, Collection $resets, Collection $stationNames): array
    {
        // Index once. Filtering the complete readings collection for every shift
        // becomes extremely expensive on month/year dashboard ranges.
        $readingsByShift = $readings->groupBy('shift_id');
        $resetsByShift = $resets->groupBy('shift_id');

        return $shifts->map(function ($shift) use ($readingsByShift, $resetsByShift, $stationNames) {
            $shiftReadings = $readingsByShift->get($shift->id, collect());
            $shiftResets = $resetsByShift->get($shift->id, collect());
            return [
                'shift_id' => (int) $shift->id,
                'label' => Carbon::parse($shift->start_time)->format('d M').' · '.($stationNames[$shift->station_id] ?? 'Unknown station').' · S'.$shift->shift_no,
                'litres' => (float) ($shiftReadings->sum('total_dispensed') + $shiftResets->sum('total_dispensed')),
                'amount' => (float) ($shiftReadings->sum('total_amount') + $shiftResets->sum('total_amount')),
                'reading_count' => $shiftReadings->count(),
            ];
        })->values()->all();
    }

    private function tankLevels(array $shiftIds, array $stationIds, ?int $productId): array
    {
        $latestDipIds = DB::table('tanks_dip')
            ->whereIn('station_id', $stationIds)
            ->when($shiftIds, fn ($query) => $query->whereIn('shift_id', $shiftIds))
            ->selectRaw('MAX(id)')
            ->groupBy('tank_id');

        $inventoryStock = DB::table('fuel_inventory_layers')
            ->selectRaw('tank_id, SUM(remaining_qty) as current_stock')
            ->where('remaining_qty', '>', 0)
            ->groupBy('tank_id');

        return DB::table('tanks as tank')
            ->join('products as product', 'product.id', '=', 'tank.product_id')
            ->join('stations as station', 'station.id', '=', 'tank.station_id')
            ->leftJoin('tanks_dip as dip', function ($join) use ($latestDipIds) {
                $join->on('dip.tank_id', '=', 'tank.id')->whereIn('dip.id', $latestDipIds);
            })
            ->leftJoinSub($inventoryStock, 'inventory', fn ($join) => $join->on('inventory.tank_id', '=', 'tank.id'))
            ->whereIn('tank.station_id', $stationIds)
            ->when($productId, fn ($query) => $query->where('tank.product_id', $productId))
            ->select('tank.id', 'tank.name as tank_name', 'tank.capacity', 'tank.station_id', 'station.name as station_name', 'product.name as product_name', 'dip.dip_in_liters', 'inventory.current_stock')
            ->orderBy('station.name')->orderBy('tank.name')
            ->get()
            ->map(fn ($row) => [
                'tank_id' => (int) $row->id,
                'tank_name' => $row->tank_name,
                'station_name' => $row->station_name,
                'product_name' => $row->product_name,
                'capacity' => (float) $row->capacity,
                'dip_litres' => (float) ($row->dip_in_liters ?? 0),
                'stock_litres' => (float) ($row->current_stock ?? 0),
                'fill_percentage' => (float) ($row->capacity > 0 ? min(100, max(0, ($row->current_stock ?? 0) / $row->capacity * 100)) : 0),
            ])->all();
    }

    private function tankVariances(array $shiftIds, array $stationIds, ?int $productId): Collection
    {
        if (!$shiftIds) return collect();

        $latestDipIds = DB::table('tanks_dip')->whereIn('shift_id', $shiftIds)
            ->selectRaw('MAX(id)')->groupBy('shift_id', 'tank_id');

        $dips = DB::table('tanks_dip as dip')
            ->join('tanks as tank', 'tank.id', '=', 'dip.tank_id')
            ->join('products as product', 'product.id', '=', 'tank.product_id')
            ->join('stations as station', 'station.id', '=', 'tank.station_id')
            ->whereIn('dip.id', $latestDipIds)
            ->whereIn('tank.station_id', $stationIds)
            ->when($productId, fn ($query) => $query->where('tank.product_id', $productId))
            ->select('dip.shift_id', 'dip.tank_id', 'dip.old_dip_liters', 'dip.dip_in_liters', 'tank.name as tank_name', 'tank.product_id', 'tank.station_id', 'station.name as station_name', 'product.name as product_name')
            ->get();

        $oilReceived = DB::table('oil_recived_tanks')->whereIn('shift_id', $shiftIds)
            ->selectRaw('shift_id, tanks_id, SUM(recived_qty) as litres')->groupBy('shift_id', 'tanks_id')->get()
            ->keyBy(fn ($row) => $row->shift_id.'|'.$row->tanks_id);

        $normalSales = DB::table('shift_nozzle_readings as reading')->join('nozzles as nozzle', 'nozzle.id', '=', 'reading.nozzle_id')
            ->whereIn('reading.shift_id', $shiftIds)
            ->selectRaw('reading.shift_id, nozzle.tank_id, SUM(reading.total_dispensed) as litres, MAX(reading.rate) as rate')
            ->groupBy('reading.shift_id', 'nozzle.tank_id')->get()->keyBy(fn ($row) => $row->shift_id.'|'.$row->tank_id);

        $resetSales = DB::table('nozzle_totalizer_resets as reset')->join('nozzles as nozzle', 'nozzle.id', '=', 'reset.nozzle_id')
            ->whereIn('reset.shift_id', $shiftIds)
            ->selectRaw('reset.shift_id, nozzle.tank_id, SUM(reset.total_dispensed) as litres, MAX(reset.rate) as rate')
            ->groupBy('reset.shift_id', 'nozzle.tank_id')->get()->keyBy(fn ($row) => $row->shift_id.'|'.$row->tank_id);

        return $dips->map(function ($dip) use ($oilReceived, $normalSales, $resetSales) {
            $key = $dip->shift_id.'|'.$dip->tank_id;
            $received = (float) ($oilReceived[$key]->litres ?? 0);
            $normal = (float) ($normalSales[$key]->litres ?? 0);
            $reset = (float) ($resetSales[$key]->litres ?? 0);
            $rate = (float) ($normalSales[$key]->rate ?? $resetSales[$key]->rate ?? 0);
            $physicalUsage = (float) $dip->old_dip_liters - (float) $dip->dip_in_liters;
            $adjustedUsage = $physicalUsage + $received;
            $variance = ($normal + $reset) - $adjustedUsage;

            return [
                'shift_id' => (int) $dip->shift_id,
                'tank_id' => (int) $dip->tank_id,
                'tank_name' => $dip->tank_name,
                'station_id' => (int) $dip->station_id,
                'station_name' => $dip->station_name,
                'product_id' => (int) $dip->product_id,
                'product_name' => $dip->product_name,
                'opening_litres' => (float) $dip->old_dip_liters,
                'closing_litres' => (float) $dip->dip_in_liters,
                'oil_received_litres' => $received,
                'nozzle_sales_litres' => $normal,
                'reset_sales_litres' => $reset,
                'variance_litres' => $variance,
                'variance_value' => $variance * $rate,
                'status' => abs($variance) < 0.01 ? 'balanced' : ($variance > 0 ? 'gain' : 'loss'),
            ];
        });
    }

    private function stationFinance(array $stationIds, Collection $stationNames, Collection $transactions, Collection $credits, Collection $variances): array
    {
        $transactionsByStation = $transactions->groupBy('station_id');
        $creditsByStation = $credits->groupBy('station_id');
        $variancesByStation = $variances->groupBy('station_id');

        $finance = collect($stationIds)->map(function ($stationId) use ($stationNames, $transactionsByStation, $creditsByStation, $variancesByStation) {
            $stationTransactions = $transactionsByStation->get($stationId, collect());
            $stationCredits = $creditsByStation->get($stationId, collect());
            $stationVariances = $variancesByStation->get($stationId, collect());
            return [
                'station_id' => (int) $stationId,
                'station_name' => $stationNames[$stationId] ?? 'Unknown station',
                'income' => (float) $stationTransactions->where('type', 'income')->sum('credit'),
                'expense' => (float) $stationTransactions->where('type', 'expense')->sum('debit'),
                'credit' => (float) $stationCredits->sum('amount'),
                'loss' => abs((float) $stationVariances->where('variance_litres', '<', 0)->sum('variance_value')),
                'gain' => (float) $stationVariances->where('variance_litres', '>', 0)->sum('variance_value'),
            ];
        });

        $knownStationIds = collect($stationIds)->map(fn ($id) => (int) $id);
        $unknownStationIds = $transactionsByStation->keys()
            ->merge($creditsByStation->keys())
            ->merge($variancesByStation->keys())
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn ($id) => $knownStationIds->contains($id));

        foreach ($unknownStationIds as $stationId) {
            $stationTransactions = $transactionsByStation->get($stationId, collect());
            $stationCredits = $creditsByStation->get($stationId, collect());
            $stationVariances = $variancesByStation->get($stationId, collect());
            $finance->push([
                'station_id' => (int) $stationId,
                'station_name' => 'Unknown station #'.$stationId,
                'income' => (float) $stationTransactions->where('type', 'income')->sum('credit'),
                'expense' => (float) $stationTransactions->where('type', 'expense')->sum('debit'),
                'credit' => (float) $stationCredits->sum('amount'),
                'loss' => abs((float) $stationVariances->where('variance_litres', '<', 0)->sum('variance_value')),
                'gain' => (float) $stationVariances->where('variance_litres', '>', 0)->sum('variance_value'),
            ]);
        }

        return $finance->values()->all();
    }

    private function records(Collection $shifts, Collection $readings, Collection $transactions, Collection $credits, Collection $variances, Collection $stationNames): array
    {
        return [
            'nozzle_readings' => $readings->map(fn ($row) => [
                'station' => $row->station_name ?? 'Unknown station', 'shift' => $row->shift_no, 'dispenser' => $row->dispenser_name ?? 'Unknown dispenser',
                'nozzle' => $row->nozzle_name ?? ('Nozzle #'.$row->nozzle_id), 'product' => $row->product_name ?? 'Unknown product', 'opening' => (float) $row->opening_reading,
                'closing' => (float) $row->closing_reading, 'litres' => (float) $row->total_dispensed,
                'rate' => (float) $row->rate, 'amount' => (float) $row->total_amount,
            ])->values()->all(),
            'shifts' => $shifts->map(fn ($row) => [
                'id' => (int) $row->id, 'station' => $stationNames[$row->station_id] ?? 'Unknown station', 'shift' => $row->shift_no,
                'start' => $row->start_time, 'end' => $row->end_time, 'status' => $row->status,
            ])->values()->all(),
            'transactions' => $transactions->map(fn ($row) => [
                'station' => $stationNames[$row->station_id] ?? 'Unknown station', 'shift' => $row->shift_no,
                'account' => $row->account_name, 'type' => $row->type, 'method' => $row->method,
                'amount' => (float) ($row->type === 'income' ? $row->credit : $row->debit),
                'note' => $row->note, 'date' => $row->created_at,
            ])->values()->all(),
            'party_credit' => $credits->map(fn ($row) => [
                'station' => $stationNames[$row->station_id] ?? 'Unknown station', 'shift' => $row->shift_no,
                'customer' => $row->account_name, 'given_to' => $row->amount_given_to,
                'vehicle' => $row->vehicle_number, 'amount' => (float) $row->amount, 'date' => $row->created_at,
            ])->values()->all(),
            'tank_variance' => $variances->values()->all(),
        ];
    }

    private function emptyResponse(Carbon $from, Carbon $to, Collection $stations, Collection $products): array
    {
        return [
            'success' => true,
            'meta' => ['from_date' => $from->toDateString(), 'to_date' => $to->toDateString(), 'generated_at' => now()->toIso8601String()],
            'filters' => ['stations' => $stations, 'products' => $products],
            'summary' => array_fill_keys(['fuel_sales', 'litres_sold', 'shift_count', 'cash_collected', 'active_nozzles', 'party_credit', 'stock_loss', 'stock_gain', 'income', 'expenses', 'net_income'], 0),
            'product_summary' => [], 'sales_trend' => [], 'nozzle_sales' => [], 'station_performance' => [],
            'shift_activity' => [], 'cash_distribution' => ['cash' => 0, 'bank' => 0, 'fuel_card' => 0, 'credit_card' => 0],
            'tank_levels' => [], 'tank_variances' => [], 'station_finance' => [],
            'records' => ['nozzle_readings' => [], 'shifts' => [], 'transactions' => [], 'party_credit' => [], 'tank_variance' => []],
        ];
    }
}
