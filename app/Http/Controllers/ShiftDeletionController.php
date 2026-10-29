<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ShiftDeletionController extends Controller
{
    public function preview(Request $request, int $shiftId): JsonResponse
    {
        $this->ensureAdmin($request);

        $shift = DB::table('shifts as s')
            ->leftJoin('stations as st', 'st.id', '=', 's.station_id')
            ->where('s.id', $shiftId)
            ->select('s.*', 'st.name as station_name')
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'Shift not found.'], 404);
        }

        $latest = DB::table('shifts')
            ->where('station_id', $shift->station_id)
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->first();

        $readingIds = DB::table('shift_nozzle_readings')->where('shift_id', $shiftId)->pluck('id');
        $siteBalanceIds = $this->siteBalanceIds((int) $shift->station_id, $shiftId);

        $counts = [
            'nozzle_readings' => $readingIds->count(),
            'fifo_consumptions' => $readingIds->isEmpty() ? 0 : DB::table('fuel_layer_consumptions')->whereIn('sale_id', $readingIds)->count(),
            'tank_dips' => DB::table('tanks_dip')->where('shift_id', $shiftId)->count(),
            'nozzle_resets' => DB::table('nozzle_totalizer_resets')->where('shift_id', $shiftId)->count(),
            'oil_receipts' => DB::table('oil_recived_tanks')->where('shift_id', $shiftId)->count(),
            'oil_purchases' => DB::table('oil_purchase')->where('shift_id', $shiftId)->count(),
            'transactions' => DB::table('transactions')->where('shift_id', $shiftId)->count(),
            'cash_flow' => DB::table('shift_cash_flow')->where('shift_id', $shiftId)->count(),
            'driver_credits' => DB::table('credit_driver')->where('shift_id', $shiftId)->count(),
            'payments' => DB::table('ammount_paid')->where('shift_id', $shiftId)->count(),
            'shortage_payments' => DB::table('shortage_ammount_paid_back')->where('shift_id', $shiftId)->count(),
            'lube_documents' => DB::table('lube_documents')->where('shift_id', $shiftId)->count(),
            'attendance' => DB::table('attendance')->where('shift_id', $shiftId)->count(),
            'site_balances' => $siteBalanceIds->count(),
        ];

        return response()->json([
            'shift' => [
                'id' => (int) $shift->id,
                'station' => $shift->station_name,
                'start_time' => $shift->start_time,
                'status' => $shift->status,
            ],
            'can_delete' => $latest && (int) $latest->id === $shiftId,
            'message' => $latest && (int) $latest->id === $shiftId
                ? 'Shift can be deleted. All listed records will be reversed atomically.'
                : 'Only the latest shift of a station can be deleted.',
            'confirmation' => "Faisal-{$shiftId}",
            'counts' => $counts,
        ]);
    }

    public function destroy(Request $request, int $shiftId): JsonResponse
    {
        $this->ensureAdmin($request);
        $request->validate(['confirmation' => 'required|string']);

        if ($request->input('confirmation') !== "Faisal-{$shiftId}") {
            return response()->json(['message' => "Type Faisal-{$shiftId} to confirm."], 422);
        }

        DB::beginTransaction();

        try {
            $shift = DB::table('shifts')->where('id', $shiftId)->lockForUpdate()->first();
            if (!$shift) {
                throw new RuntimeException('Shift not found.', 404);
            }

            DB::table('stations')->where('id', $shift->station_id)->lockForUpdate()->first();
            $latest = DB::table('shifts')
                ->where('station_id', $shift->station_id)
                ->orderByDesc('start_time')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$latest || (int) $latest->id !== $shiftId) {
                throw new RuntimeException('Only the latest shift of a station can be deleted.', 409);
            }

            $summary = [
                'fifo_litres_restored' => 0,
                'inventory_layers_removed' => 0,
                'site_balance_records_removed' => 0,
            ];
            $siteBalanceIds = $this->siteBalanceIds((int) $shift->station_id, $shiftId);

            $readings = DB::table('shift_nozzle_readings')->where('shift_id', $shiftId)->lockForUpdate()->get();
            foreach ($readings as $reading) {
                $consumptions = DB::table('fuel_layer_consumptions')
                    ->where('sale_id', $reading->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($consumptions as $consumption) {
                    $updated = DB::table('fuel_inventory_layers')
                        ->where('id', $consumption->layer_id)
                        ->increment('remaining_qty', $consumption->qty);
                    if (!$updated) {
                        throw new RuntimeException("FIFO layer {$consumption->layer_id} is missing.", 409);
                    }
                    $summary['fifo_litres_restored'] += (float) $consumption->qty;
                }
                DB::table('fuel_layer_consumptions')->where('sale_id', $reading->id)->delete();
            }

            $receipts = DB::table('oil_recived_tanks')->where('shift_id', $shiftId)->lockForUpdate()->get();
            $shiftPayments = DB::table('ammount_paid')->where('shift_id', $shiftId)->lockForUpdate()->get();
            $paidOilPurchaseIds = $shiftPayments->pluck('oil_purchase_id')->filter()->unique()->values();
            $paidLubeIds = $shiftPayments->pluck('lube_id')->filter()->unique()->values();
            $purchaseIds = $receipts->pluck('oil_purchase_id')
                ->merge(DB::table('oil_purchase')->where('shift_id', $shiftId)->pluck('id'))
                ->merge($paidOilPurchaseIds)
                ->unique()->values();

            foreach ($receipts as $receipt) {
                $purchase = DB::table('oil_purchase')->where('id', $receipt->oil_purchase_id)->lockForUpdate()->first();
                if (!$purchase) {
                    throw new RuntimeException("Oil purchase {$receipt->oil_purchase_id} is missing.", 409);
                }

                $layersQuery = DB::table('fuel_inventory_layers')
                    ->where('tank_id', $receipt->tanks_id)
                    ->where('created_at', $receipt->created_at);

                if ($purchase->product_id !== null) {
                    $layersQuery->where('product_id', $purchase->product_id);
                }
                if ($purchase->rate !== null) {
                    $layersQuery->where('rate', $purchase->rate);
                }

                $layers = $layersQuery->lockForUpdate()->get();
                if ($layers->isEmpty()) {
                    throw new RuntimeException("Inventory layer for oil receipt {$receipt->id} was not found.", 409);
                }

                $layerQuantity = (float) $layers->sum('remaining_qty');
                if (abs($layerQuantity - (float) $receipt->recived_qty) > 0.01) {
                    throw new RuntimeException(
                        "Oil receipt {$receipt->id} cannot be reversed: layer has {$layerQuantity} L, expected {$receipt->recived_qty} L.",
                        409
                    );
                }

                DB::table('fuel_inventory_layers')->whereIn('id', $layers->pluck('id'))->delete();
                $summary['inventory_layers_removed'] += $layers->count();
            }

            $dips = DB::table('tanks_dip')->where('shift_id', $shiftId)->lockForUpdate()->get();
            foreach ($dips->groupBy('tank_id') as $tankId => $tankDips) {
                $dip = $tankDips->sortByDesc('id')->first();
                DB::table('tanks')->where('id', $tankId)->update([
                    'current_level' => $dip->old_dip_liters,
                    'current_level_mm' => $dip->old_dip_mm,
                    'updated_at' => now(),
                ]);
            }

            foreach ($readings->groupBy('nozzle_id') as $nozzleId => $nozzleReadings) {
                $reading = $nozzleReadings->sortByDesc('id')->first();
                DB::table('nozzles')->where('id', $nozzleId)->update([
                    'intial_meter_reading' => $reading->opening_reading,
                    'updated_at' => now(),
                ]);
            }

            $this->reverseLubeInventory($shiftId);
            $this->reverseDriverCreditPayments($shiftId);

            DB::table('nozzle_totalizer_resets')->where('shift_id', $shiftId)->delete();
            DB::table('shift_nozzle_readings')->where('shift_id', $shiftId)->delete();
            DB::table('tanks_dip')->where('shift_id', $shiftId)->delete();
            DB::table('credit_driver')->where('shift_id', $shiftId)->delete();
            DB::table('transactions')->where('shift_id', $shiftId)->delete();
            DB::table('attendance')->where('shift_id', $shiftId)->delete();
            DB::table('shortage_ammount_paid_back')->where('shift_id', $shiftId)->delete();
            DB::table('ammount_paid')->where('shift_id', $shiftId)->delete();
            DB::table('product_prices')->where('shift_id', $shiftId)->delete();
            DB::table('oil_recived_tanks')->where('shift_id', $shiftId)->delete();

            foreach ($purchaseIds as $purchaseId) {
                $remainingReceived = (float) DB::table('oil_recived_tanks')
                    ->where('oil_purchase_id', $purchaseId)
                    ->sum('recived_qty');
                $purchase = DB::table('oil_purchase')->where('id', $purchaseId)->lockForUpdate()->first();
                if (!$purchase) {
                    continue;
                }

                if ((int) $purchase->shift_id === $shiftId && $remainingReceived <= 0) {
                    DB::table('shortage_ammount_paid_back')->where('oil_purchase_id', $purchaseId)->delete();
                    DB::table('ammount_paid')->where('oil_purchase_id', $purchaseId)->delete();
                    DB::table('oil_purchase')->where('id', $purchaseId)->delete();
                } else {
                    DB::table('oil_purchase')->where('id', $purchaseId)->update([
                        'recieved_qty' => $remainingReceived,
                        'stock_update' => $remainingReceived > 0 ? 1 : 0,
                        'recive_status' => $remainingReceived > 0 ? 'Recived' : 'Not-Recived',
                        'updated_at' => now(),
                    ]);
                }
            }

            $this->recalculatePurchasePaymentStatuses($paidOilPurchaseIds);
            $this->recalculateLubePaymentStatuses($paidLubeIds);
            $this->reverseSiteBalances($shift, $summary, $siteBalanceIds);
            DB::table('shift_cash_flow')->where('shift_id', $shiftId)->delete();
            DB::table('shifts')->where('id', $shiftId)->delete();
            DB::commit();

            Log::warning('Shift permanently deleted with reversal', [
                'shift_id' => $shiftId,
                'station_id' => $shift->station_id,
                'deleted_by' => $request->user()->id,
                'summary' => $summary,
            ]);

            return response()->json([
                'message' => 'Shift and all related records were reversed and deleted successfully.',
                'summary' => $summary,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Atomic shift deletion failed', [
                'shift_id' => $shiftId,
                'deleted_by' => optional($request->user())->id,
                'error' => $e->getMessage(),
            ]);

            $status = $e instanceof RuntimeException && $e->getCode() >= 400 && $e->getCode() < 600
                ? $e->getCode()
                : 500;

            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    private function reverseSiteBalances(object $shift, array &$summary, $recordIds = null): void
    {
        $recordIds = $recordIds ?: $this->siteBalanceIds((int) $shift->station_id, (int) $shift->id);
        if ($recordIds->isEmpty()) {
            return;
        }

        $records = DB::table('site_total_ammount')
            ->whereIn('id', $recordIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($records as $record) {
            $added = (float) $record->amount - (float) $record->previous_amount;
            DB::table('site_total_ammount')
                ->where('station_id', $record->station_id)
                ->where('account_id', $record->account_id)
                ->where('id', '>', $record->id)
                ->update([
                    'amount' => DB::raw('amount - ' . $added),
                    'previous_amount' => DB::raw('previous_amount - ' . $added),
                ]);
            $deleted = DB::table('site_total_ammount')->where('id', $record->id)->delete();
            if ($deleted !== 1) {
                throw new RuntimeException("Site balance record {$record->id} could not be deleted.", 409);
            }
            if (DB::table('site_total_ammount')->where('id', $record->id)->exists()) {
                throw new RuntimeException("Site balance record {$record->id} still exists after deletion.", 409);
            }
            $summary['site_balance_records_removed'] += $deleted;
        }
    }

    private function siteBalanceIds(int $stationId, int $shiftId)
    {
        $cashFlows = DB::table('shift_cash_flow')
            ->where('shift_id', $shiftId)
            ->orderBy('id')
            ->get(['created_at']);

        $ids = $cashFlows->flatMap(function ($cashFlow) use ($stationId) {
            return DB::table('site_total_ammount')
                ->where('station_id', $stationId)
                ->whereBetween('created_at', $this->paymentTimeWindow($cashFlow->created_at))
                ->pluck('id');
        });

        $paymentEvents = DB::table('ammount_paid')
            ->where('shift_id', $shiftId)
            ->where('method', 'bank')
            ->whereNotNull('account_id')
            ->get(['account_id', 'created_at']);

        $driverPaymentEvents = DB::table('credit_driver')
            ->where('shift_id', $shiftId)
            ->where('is_paid', 1)
            ->where('method', 'bank')
            ->whereNotNull('bank_acc_id')
            ->get(['bank_acc_id as account_id', 'updated_at as created_at']);

        $shortagePaymentEvents = DB::table('shortage_ammount_paid_back')
            ->where('shift_id', $shiftId)
            ->whereNotNull('account_id')
            ->get(['account_id', 'created_at']);

        foreach ($paymentEvents->concat($driverPaymentEvents)->concat($shortagePaymentEvents) as $event) {
            if (!$event->created_at) {
                continue;
            }
            $ids = $ids->merge(
                DB::table('site_total_ammount')
                    ->where('station_id', $stationId)
                    ->where('account_id', $event->account_id)
                    ->whereBetween('created_at', $this->paymentTimeWindow($event->created_at))
                    ->pluck('id')
            );
        }

        return $ids->unique()->sort()->values();
    }

    private function paymentTimeWindow(string $createdAt): array
    {
        $timestamp = strtotime($createdAt);

        return [
            date('Y-m-d H:i:s', $timestamp - 2),
            date('Y-m-d H:i:s', $timestamp + 2),
        ];
    }

    private function reverseLubeInventory(int $shiftId): void
    {
        $documents = DB::table('lube_documents')->where('shift_id', $shiftId)->lockForUpdate()->get();
        foreach ($documents as $document) {
            $lines = DB::table('lube_lines')->where('document_id', $document->id)->get();
            foreach ($lines as $line) {
                $inventory = DB::table('lube_inventory')
                    ->where('station_id', $document->station_id)
                    ->where('product_id', $line->product_id)
                    ->lockForUpdate()
                    ->first();
                if (!$inventory) {
                    throw new RuntimeException("Lube inventory is missing for document {$document->id}.", 409);
                }

                if ($document->doc_type === 'purchase') {
                    if ((float) $inventory->quantity < (float) $line->qty) {
                        throw new RuntimeException("Lube purchase {$document->id} has already been consumed and cannot be reversed.", 409);
                    }
                    DB::table('lube_inventory')->where('id', $inventory->id)->update([
                        'quantity' => DB::raw('quantity - ' . (float) $line->qty),
                        'total_purchased' => DB::raw('GREATEST(total_purchased - ' . (float) $line->qty . ', 0)'),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('lube_inventory')->where('id', $inventory->id)->update([
                        'quantity' => DB::raw('quantity + ' . (float) $line->qty),
                        'total_sold' => DB::raw('GREATEST(total_sold - ' . (float) $line->qty . ', 0)'),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('lube_inventory_logs')->where('document_id', $document->id)->delete();
            DB::table('ammount_paid')->where('lube_id', $document->id)->delete();
            DB::table('lube_lines')->where('document_id', $document->id)->delete();
            DB::table('lube_documents')->where('id', $document->id)->delete();
        }
    }

    private function recalculatePurchasePaymentStatuses($purchaseIds): void
    {
        foreach ($purchaseIds as $purchaseId) {
            $purchase = DB::table('oil_purchase')->where('id', $purchaseId)->first();
            if (!$purchase) {
                continue;
            }

            $paid = (float) DB::table('ammount_paid')
                ->where('oil_purchase_id', $purchaseId)
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'debit' THEN ammount ELSE -ammount END), 0) total")
                ->value('total');
            $total = (float) $purchase->qty * (float) $purchase->rate;
            $status = $paid <= 0 ? 'not_paid' : ($paid + 0.01 >= $total ? 'paid' : 'partial');

            DB::table('oil_purchase')->where('id', $purchaseId)->update([
                'payment_status' => $status,
                'updated_at' => now(),
            ]);
        }
    }

    private function recalculateLubePaymentStatuses($documentIds): void
    {
        foreach ($documentIds as $documentId) {
            if (!DB::table('lube_documents')->where('id', $documentId)->exists()) {
                continue;
            }

            $total = (float) DB::table('lube_lines')
                ->where('document_id', $documentId)
                ->selectRaw('COALESCE(SUM(line_amount + tax_amount), 0) total')
                ->value('total');
            $paid = (float) DB::table('ammount_paid')
                ->where('lube_id', $documentId)
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'debit' THEN ammount ELSE -ammount END), 0) total")
                ->value('total');
            $status = $paid <= 0 ? 'not_paid' : ($paid + 0.01 >= $total ? 'paid' : 'partial');

            DB::table('lube_documents')->where('id', $documentId)->update([
                'payment_status' => $status,
                'updated_at' => now(),
            ]);
        }
    }

    private function reverseDriverCreditPayments(int $shiftId): void
    {
        $credits = DB::table('credit_driver')
            ->where('shift_id', $shiftId)
            ->where('is_paid', 1)
            ->lockForUpdate()
            ->get();

        foreach ($credits as $credit) {
            $originalShiftId = DB::table('shift_cash_flow as cf')
                ->join('shifts as s', 's.id', '=', 'cf.shift_id')
                ->where('s.station_id', $credit->station_id)
                ->where('cf.shift_id', '!=', $shiftId)
                ->whereBetween('cf.created_at', $this->paymentTimeWindow($credit->created_at))
                ->orderByDesc('cf.id')
                ->value('cf.shift_id');

            if ($originalShiftId) {
                DB::table('credit_driver')->where('id', $credit->id)->update([
                    'shift_id' => $originalShiftId,
                    'is_paid' => 0,
                    'method' => null,
                    'bank_acc_id' => null,
                    'updated_at' => now(),
                ]);
                continue;
            }

            $createdInDeletedShift = DB::table('shift_cash_flow')
                ->where('shift_id', $shiftId)
                ->whereBetween('created_at', $this->paymentTimeWindow($credit->created_at))
                ->exists();

            if (!$createdInDeletedShift) {
                throw new RuntimeException(
                    "Driver credit {$credit->id} was paid in this shift, but its original shift could not be determined.",
                    409
                );
            }
        }
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user() && $request->user()->role === 'admin', 403, 'Only admin can delete shifts.');
    }
}
