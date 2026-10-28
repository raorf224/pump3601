<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AtomicShiftCloseController extends Controller
{
    public function store(Request $request, int $shiftId): JsonResponse
    {
        $validated = $request->validate([
            'end_time' => 'required|date',
            'cash_return' => 'required|numeric|min:0',
            'tank_data' => 'present|array',
            'nozzle_readings' => 'present|array',
            'nozzle_resets' => 'present|array',
            'payments' => 'present|array',
            'driver_data' => 'present|array',
            'expenses' => 'present|array',
            'cash_flow' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $shift = DB::table('shifts')->where('id', $shiftId)->lockForUpdate()->first();

            if (!$shift) {
                $this->rollbackAll();
                return response()->json(['message' => 'Shift not found.'], 404);
            }

            if ($shift->status === 'closed') {
                $this->rollbackAll();
                return response()->json(['message' => 'This shift has already been closed.'], 409);
            }

            if (strtotime($validated['end_time']) <= strtotime($shift->start_time)) {
                $this->rollbackAll();
                return response()->json(['message' => 'End time must be after shift start time.'], 422);
            }

            foreach ($validated['nozzle_readings'] as $reading) {
                $this->runOperation(
                    fn () => app(ShiftNozzleReadingsController::class)->store($this->childRequest($reading)),
                    'nozzle reading'
                );
            }

            if ($validated['tank_data']) {
                $this->runOperation(
                    fn () => app(TanksDipController::class)->storeTankDipReadings(
                        $this->childRequest(['tank_data' => $validated['tank_data']])
                    ),
                    'tank dip readings'
                );
            }

            foreach ($validated['nozzle_resets'] as $reset) {
                $this->runOperation(
                    fn () => app(NozzleTotalizerResetController::class)->store($this->childRequest($reset)),
                    'nozzle reset'
                );
            }

            foreach ($validated['payments'] as $payment) {
                $this->runOperation(
                    fn () => app(SiteTotalAmountController::class)->storePaymentDetails($this->childRequest($payment)),
                    'payment'
                );
            }

            if ($validated['driver_data']) {
                $this->runOperation(
                    fn () => app(DriverCreditController::class)->store(
                        $this->childRequest(['driver_data' => $validated['driver_data']])
                    ),
                    'driver credit'
                );
            }

            foreach ($validated['expenses'] as $expense) {
                $this->runOperation(
                    fn () => app(TransactionsController::class)->store($this->childRequest($expense)),
                    'expense'
                );
            }

            $this->runOperation(
                fn () => app(ShiftController::class)->saveCashFlow($this->childRequest($validated['cash_flow'])),
                'cash flow'
            );

            DB::table('shifts')->where('id', $shiftId)->update([
                'end_time' => $validated['end_time'],
                'cash_return' => round((float) $validated['cash_return'], 2),
                'status' => 'closed',
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Shift and all closing records saved successfully.',
                'shift_id' => $shiftId,
            ]);
        } catch (ValidationException $e) {
            $this->rollbackAll();

            return response()->json([
                'message' => 'Closing data validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            $this->rollbackAll();

            Log::error('Atomic shift close failed', [
                'shift_id' => $shiftId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            $status = $e instanceof RuntimeException && $e->getCode() >= 400 && $e->getCode() < 600
                ? $e->getCode()
                : 500;

            return response()->json([
                'message' => $e->getMessage() ?: 'Shift closing failed. No records were saved.',
            ], $status);
        }
    }

    private function childRequest(array $data): Request
    {
        $request = Request::create('/', 'POST', $data);
        $request->setUserResolver(fn () => auth()->user());

        return $request;
    }

    private function runOperation(callable $operation, string $label): void
    {
        $response = $operation();
        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 500;

        if ($status < 400) {
            return;
        }

        $payload = method_exists($response, 'getData') ? $response->getData(true) : [];
        $message = $payload['message'] ?? "Failed to save {$label}.";
        throw new RuntimeException($message, $status);
    }

    private function rollbackAll(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    }
}
