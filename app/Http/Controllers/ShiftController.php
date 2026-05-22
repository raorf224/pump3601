<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('shifts')
            ->join('stations', 'shifts.station_id', '=', 'stations.id')
            ->leftJoin('employees', 'shifts.shift_incharger', '=', 'employees.id')
            ->leftJoin('users', 'employees.user_id', '=', 'users.id')
            ->select(
                'shifts.*',
                'stations.name as station_name',
                'users.full_name as shift_incharger_name',
                DB::raw("CASE WHEN shifts.shift_no = 1 THEN 'Day' ELSE 'Night' END AS shift_type")
            )
            ->orderBy('shifts.id', 'desc');

        if ($request->has('station_id')) {
            $query->where('shifts.station_id', $request->station_id);
        }

        $shifts = $query->get();

        // ✅ Frontend ke liye time format karo (database time ko as it is show karo)
        $shifts->transform(function ($shift) {
            if ($shift->start_time) {
                $shift->start_time = Carbon::parse($shift->start_time)->format('Y-m-d H:i:s');
            }
            if ($shift->end_time) {
                $shift->end_time = Carbon::parse($shift->end_time)->format('Y-m-d H:i:s');
            }
            return $shift;
        });

        return $shifts->isEmpty()
            ? response()->json(['message' => 'No shifts found'], 404)
            : response()->json($shifts);
    }

    public function index1($user_id, Request $request)
    {
        $query = DB::table('shifts')
            ->join('stations', 'shifts.station_id', '=', 'stations.id')
            ->leftJoin('employees', 'shifts.shift_incharger', '=', 'employees.id')
            ->leftJoin('users', 'employees.user_id', '=', 'users.id')
            ->select(
                'shifts.*',
                'stations.name as station_name',
                'users.full_name as shift_incharger_name',
                DB::raw("CASE WHEN shifts.shift_no = 1 THEN 'Day' ELSE 'Night' END AS shift_type")
            )
            ->where('stations.user_id', $user_id)
            ->orderBy('shifts.id', 'desc');

        if ($request->has('station_id')) {
            $query->where('shifts.station_id', $request->station_id);
        }

        $shifts = $query->get();

        // ✅ Frontend ke liye time format karo (database time ko as it is show karo)
        $shifts->transform(function ($shift) {
            if ($shift->start_time) {
                $shift->start_time = Carbon::parse($shift->start_time)->format('Y-m-d H:i:s');
            }
            if ($shift->end_time) {
                $shift->end_time = Carbon::parse($shift->end_time)->format('Y-m-d H:i:s');
            }
            return $shift;
        });

        return $shifts->isEmpty()
            ? response()->json(['message' => 'No shifts found'], 404)
            : response()->json($shifts);
    }

    public function show($id)
    {
        $shift = DB::table('shifts')
            ->join('stations', 'shifts.station_id', '=', 'stations.id')
            ->leftJoin('employees', 'shifts.shift_incharger', '=', 'employees.id')
            ->leftJoin('users', 'employees.user_id', '=', 'users.id')
            ->select(
                'shifts.*',
                'stations.name as station_name',
                'users.full_name as shift_incharger_name',
                DB::raw("CASE WHEN shifts.shift_no = 1 THEN 'Day' ELSE 'Night' END AS shift_type")
            )
            ->where('shifts.id', $id)
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }

        // ✅ Edit form ke liye time format karo (datetime-local input ke liye)
        if ($shift->start_time) {
            $shift->start_time = Carbon::parse($shift->start_time)->format('Y-m-d\TH:i');
        }
        if ($shift->end_time) {
            $shift->end_time = Carbon::parse($shift->end_time)->format('Y-m-d\TH:i');
        }

        return response()->json($shift);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|integer',
            'shift_no' => 'required|in:1,2',
            'shift_incharger' => 'required|integer',
            'start_time' => 'required|date',
            'cash_handover' => 'required|numeric|min:0',
            'status' => 'required|in:open,closed',
            'end_time' => 'nullable|date',
        ]);

        // ✅ Get the latest shift end time for this station
        $latestShift = DB::table('shifts')
            ->where('station_id', $validated['station_id'])
            ->orderBy('created_at', 'desc')
            ->first();

        // ✅ If previous shift exists, validate start_time
        if ($latestShift && $latestShift->end_time) {
            $minStartTime = Carbon::parse($latestShift->end_time)->addMinute();
            $requestedStartTime = Carbon::parse($validated['start_time']);

            if ($requestedStartTime->lt($minStartTime)) {
                return response()->json([
                    'message' => 'Start time must be at least 1 minute after the end time of previous shift',
                    'min_start_time' => $minStartTime->format('Y-m-d\TH:i'),
                    'previous_shift_end' => $latestShift->end_time
                ], 422);
            }
        }

        // ✅ Ensure decimal values are properly formatted for DECIMAL(15,2)
        // Convert to float first, then format with 2 decimal places
        $cashHandover = round((float) $validated['cash_handover'], 2);

        $id = DB::table('shifts')->insertGetId([
            'station_id' => $validated['station_id'],
            'shift_no' => $validated['shift_no'],
            'shift_incharger' => $validated['shift_incharger'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'cash_handover' => $cashHandover,
            'status' => $validated['status'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Shift created successfully',
            'shift_id' => $id,
            'cash_handover' => number_format($cashHandover, 2, '.', '')
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'station_id' => 'nullable|integer',
            'shift_no' => 'nullable|in:1,2',
            'shift_incharger' => 'nullable|integer',
            'cash_handover' => 'nullable|numeric',
            'status' => 'nullable|in:open,closed',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'cash_return' => 'nullable|numeric',
        ]);

        if (!DB::table('shifts')->where('id', $id)->exists()) {
            return response()->json(['message' => 'Shift not found'], 404);
        }

        // Get current shift details
        $currentShift = DB::table('shifts')->where('id', $id)->first();

        // If start_time is being updated, validate against previous shift
        if (isset($validated['start_time']) && $currentShift) {
            $previousShift = DB::table('shifts')
                ->where('station_id', $currentShift->station_id)
                ->where('id', '!=', $id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($previousShift && $previousShift->end_time) {
                $minStartTime = Carbon::parse($previousShift->end_time)->addMinute();
                $requestedStartTime = Carbon::parse($validated['start_time']);

                if ($requestedStartTime->lt($minStartTime)) {
                    return response()->json([
                        'message' => 'Start time must be at least 1 minute after the end time of previous shift',
                        'min_start_time' => $minStartTime->format('Y-m-d\TH:i'),
                        'previous_shift_end' => $previousShift->end_time
                    ], 422);
                }
            }
        }

        $updateData = [];

        // ✅ Process each field with proper decimal formatting
        if (isset($validated['station_id'])) {
            $updateData['station_id'] = $validated['station_id'];
        }
        if (isset($validated['shift_no'])) {
            $updateData['shift_no'] = $validated['shift_no'];
        }
        if (isset($validated['shift_incharger'])) {
            $updateData['shift_incharger'] = $validated['shift_incharger'];
        }
        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }
        if (isset($validated['start_time'])) {
            $updateData['start_time'] = $validated['start_time'];
        }
        if (isset($validated['end_time'])) {
            $updateData['end_time'] = $validated['end_time'];
        }
        if (isset($validated['cash_handover'])) {
            $updateData['cash_handover'] = round((float) $validated['cash_handover'], 2);
        }
        if (isset($validated['cash_return'])) {
            $updateData['cash_return'] = round((float) $validated['cash_return'], 2);
        }

        $updateData['updated_at'] = Carbon::now();

        if (!empty($updateData)) {
            DB::table('shifts')->where('id', $id)->update($updateData);
        }

        return response()->json([
            'message' => 'Shift updated successfully',
            'cash_handover' => isset($updateData['cash_handover']) ? number_format($updateData['cash_handover'], 2, '.', '') : null,
            'cash_return' => isset($updateData['cash_return']) ? number_format($updateData['cash_return'], 2, '.', '') : null
        ]);
    }

    // ✅ NEW FUNCTION: Get only OPEN shifts for specific station
    public function getOpenShifts($stationId)
    {
        $shifts = DB::table('shifts')
            ->join('stations', 'shifts.station_id', '=', 'stations.id')
            ->leftJoin('employees', 'shifts.shift_incharger', '=', 'employees.id')
            ->leftJoin('users', 'employees.user_id', '=', 'users.id')
            ->select(
                'shifts.*',
                'stations.name as station_name',
                'users.full_name as shift_incharger_name',
                DB::raw("CASE 
                    WHEN shifts.shift_no = 1 THEN 'Day Shift' 
                    WHEN shifts.shift_no = 2 THEN 'Night Shift' 
                    ELSE 'General Shift' 
                END as shift_type")
            )
            ->where('shifts.station_id', $stationId)
            ->where('shifts.status', 'open')
            ->orderBy('shifts.id', 'desc')
            ->get();

        return $shifts->isEmpty()
            ? response()->json(['message' => 'No open shifts found'], 404)
            : response()->json($shifts);
    }

    // ✅ NEW FUNCTION: Get last shift end time for a station
    public function getLastShiftEndTime($stationId)
    {
        $lastShift = DB::table('shifts')
            ->where('station_id', $stationId)
            ->whereNotNull('end_time')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastShift) {
            return response()->json([
                'message' => 'No previous shifts found for this station',
                'min_start_time' => null
            ], 404);
        }

        $minStartTime = Carbon::parse($lastShift->end_time)->addMinute();

        return response()->json([
            'last_shift_end' => $lastShift->end_time,
            'min_start_time' => $minStartTime->format('Y-m-d\TH:i')
        ]);
    }

    public function closeShiftPage(Request $request)
    {
        try {
            $shiftId = $request->get('shift_id');

            \Log::info('Close Shift Page Accessed', [
                'shift_id' => $shiftId,
                'full_url' => $request->fullUrl(),
                'user_id' => auth()->id()
            ]);

            if (!$shiftId) {
                // Use direct URL instead of named route
                return redirect('/shifts')->with('error', 'Shift ID not provided');
            }

            // Verify shift exists and get details
            $shift = DB::table('shifts')
                ->join('stations', 'shifts.station_id', '=', 'stations.id')
                ->leftJoin('employees', 'shifts.shift_incharger', '=', 'employees.id')
                ->leftJoin('users', 'employees.user_id', '=', 'users.id')
                ->select(
                    'shifts.*',
                    'stations.name as station_name',
                    'users.full_name as shift_incharger_name'
                )
                ->where('shifts.id', $shiftId)
                ->first();

            if (!$shift) {
                return redirect('/shifts')->with('error', 'Shift not found');
            }

            // Check if shift is already closed
            if ($shift->status === 'closed') {
                return redirect('/shifts')->with('error', 'Shift is already closed');
            }

            return view('shifts.close', [
                'shiftId' => $shiftId,
                'shift' => $shift
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in closeShiftPage: ' . $e->getMessage());
            return redirect('/shifts')->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function getOpenShiftsByStation($stationId)
    {
        $shifts = DB::table('shifts')
            ->where('station_id', $stationId)
            ->where('status', 'open')
            ->select('id', 'shift_no', 'start_time', 'shift_incharger', 'end_time', 'cash_handover', 'cash_return', 'status', 'created_at', 'updated_at')
            ->orderBy('id', 'desc')
            ->get();

        if ($shifts->isEmpty()) {
            return response()->json([
                'message' => 'No open shifts found for this station',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Open shifts fetched successfully',
            'data' => $shifts
        ], 200);
    }

    // ✅ NEW: Save Shift Cash Flow
    public function saveCashFlow(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => 'required|integer|exists:shifts,id',
            'shift_incharge' => 'required|integer|exists:employees,id',
            'total_cash' => 'required|numeric|min:0',
            'in_hand' => 'required|numeric|min:0',
            'in_bank' => 'nullable|numeric|min:0',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'creditcard' => 'nullable|numeric|min:0',
            'fuelcard' => 'nullable|numeric|min:0',
            'faccountid' => 'nullable|numeric|min:0',
            'caccountid' => 'nullable|numeric|min:0',
            'baccountid' => 'nullable|numeric|min:0',
        ]);

        // ✅ Format decimal values with 2 decimal places
        $totalCash = round((float) $validated['total_cash'], 2);
        $inHand = round((float) $validated['in_hand'], 2);
        $inBank = round((float) ($validated['in_bank'] ?? 0), 2);

        $id = DB::table('shift_cash_flow')->insertGetId([
            'shift_id' => $validated['shift_id'],
            'shift_incharge' => $validated['shift_incharge'],
            'total_cash' => $totalCash,
            'in_hand' => $inHand,
            'in_bank' => $inBank,
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'creditcard' => $validated['creditcard'],
            'fuelcard' => $validated['fuelcard'],
            'faccountid' => $validated['faccountid'],
            'caccountid' => $validated['caccountid'],
            'baccountid' => $validated['baccountid'],
        ]);

        return response()->json([
            'message' => 'Cash flow saved successfully',
            'cash_flow_id' => $id,
            'total_cash' => number_format($totalCash, 2, '.', ''),
            'in_hand' => number_format($inHand, 2, '.', ''),
            'in_bank' => number_format($inBank, 2, '.', '')
        ], 201);
    }

    // ✅ NEW: Get last shift cash return for a station
    public function getLastShiftCashReturn($stationId)
    {
        $lastShift = DB::table('shifts')
            ->where('station_id', $stationId)
            ->whereNotNull('cash_return')
            ->where('status', 'closed')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastShift) {
            return response()->json([
                'message' => 'No previous closed shifts found for this station',
                'last_cash_return' => null,
                'is_first_shift' => true
            ], 200);
        }

        return response()->json([
            'message' => 'Last shift cash return fetched successfully',
            'last_cash_return' => $lastShift->cash_return,
            'last_shift_id' => $lastShift->id,
            'is_first_shift' => false
        ], 200);
    }

    public function getCurrentAmount($stationId, $accountId)
    {
        try {
            Log::info("Getting current amount for station: $stationId, account: $accountId");

            // ✅ Latest record by ID (not created_at)
            $latestRecord = DB::table('site_total_ammount')
                ->where('station_id', $stationId)
                ->where('account_id', $accountId)
                ->orderBy('id', 'desc')  // ✅ ORDER BY ID
                ->first();

            if (!$latestRecord) {
                return response()->json([
                    'success' => true,
                    'current_balance' => 0,
                    'previous_balance' => 0,
                    'message' => 'No records found'
                ]);
            }

            return response()->json([
                'success' => true,
                'current_balance' => (float) $latestRecord->amount,  // ✅ Latest amount
                'previous_balance' => (float) $latestRecord->previous_amount,
                'last_updated' => $latestRecord->date,
                'record_id' => $latestRecord->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getCurrentAmount: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeSiteTotalAmount(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|integer',
            'account_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',      // ✅ NEW BALANCE
            'previous_amount' => 'required|numeric|min:0',   // ✅ OLD BALANCE
            'date' => 'required|date',
            'created_by' => 'nullable|integer'
        ]);

        // ✅ SIRF INSERT - NO UPDATE
        $id = DB::table('site_total_ammount')->insertGetId([
            'station_id' => $validated['station_id'],
            'account_id' => $validated['account_id'],
            'amount' => $validated['amount'],        // ✅ NEW BALANCE
            'previous_amount' => $validated['previous_amount'], // ✅ OLD BALANCE
            'date' => $validated['date'],
            'created_by' => $validated['created_by'],
            'created_at' => now()
        ]);
        // dd($validated['amount']);
        // ✅ Return SAME values that were sent
        return response()->json([
            'success' => true,
            'message' => 'Amount recorded successfully',
            'record_id' => $id,
            'account_id' => $validated['account_id'],
            'created_by' => $validated['created_by']  // ✅ Return for debugging

        ], 201);
    }

    private function canEditClosedShift($shiftId)
    {
        $shift = DB::table('shifts')->where('id', $shiftId)->first();

        if (!$shift) {
            return ['can_edit' => false, 'message' => 'Shift not found'];
        }

        if ($shift->status !== 'closed') {
            return ['can_edit' => false, 'message' => 'Only closed shifts can be edited'];
        }

        // ✅ Check if is_edited column exists before accessing
        $columns = DB::select("SHOW COLUMNS FROM shifts");
        $columnNames = array_column($columns, 'Field');

        if (in_array('is_edited', $columnNames) && isset($shift->is_edited) && $shift->is_edited == 1) {
            return ['can_edit' => false, 'message' => 'This shift has already been edited once and cannot be edited again'];
        }

        // Get the most recent closed shift for this station
        $lastClosedShift = DB::table('shifts')
            ->where('station_id', $shift->station_id)
            ->where('status', 'closed')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastClosedShift || $lastClosedShift->id != $shiftId) {
            return ['can_edit' => false, 'message' => 'Only the most recently closed shift can be edited'];
        }

        return ['can_edit' => true, 'message' => 'Can edit'];
    }

    public function editClosedShift($id)
    {
        try {
            $shift = DB::table('shifts')
                ->join('stations', 'shifts.station_id', '=', 'stations.id')
                ->leftJoin('employees', 'shifts.shift_incharger', '=', 'employees.id')
                ->leftJoin('users', 'employees.user_id', '=', 'users.id')
                ->select(
                    'shifts.*',
                    'stations.name as station_name',
                    'users.full_name as shift_incharger_name'
                )
                ->where('shifts.id', $id)
                ->first();

            if (!$shift) {
                return redirect('/shifts')->with('error', 'Shift not found');
            }

            // Check if can edit
            $editPermission = $this->canEditClosedShift($id);
            if (!$editPermission['can_edit']) {
                return redirect('/shifts')->with('error', $editPermission['message']);
            }

            // Get UNIQUE tank dips
            $tankDips = DB::table('tanks_dip')
                ->where('shift_id', $id)
                ->orderBy('id', 'desc')
                ->get()
                ->unique('tank_id')
                ->values();

            // ✅ FIX: Get UNIQUE nozzle readings (one per nozzle, not per tank)
            $nozzleReadings = DB::table('shift_nozzle_readings as snr')
                ->join('nozzles as n', 'snr.nozzle_id', '=', 'n.id')
                ->leftJoin('dispensers as d', 'n.dispenser_id', '=', 'd.id')
                ->leftJoin('products as p', 'n.product_id', '=', 'p.id')
                ->select(
                    'snr.*',
                    'n.name as nozzle_name',
                    'n.product_id',
                    'd.name as dispenser_name',
                    'p.name as product_name'
                )
                ->where('snr.shift_id', $id)
                ->distinct('snr.nozzle_id')  // ✅ DISTINCT by nozzle_id
                ->get();

            $cashFlow = DB::table('shift_cash_flow')->where('shift_id', $id)->first();
            $driverCredits = DB::table('credit_driver')->where('shift_id', $id)->get();

            // Get tank products for display
            foreach ($tankDips as $dip) {
                $tank = DB::table('tanks')
                    ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
                    ->select('tanks.*', 'products.name as product_name')
                    ->where('tanks.id', $dip->tank_id)
                    ->first();
                $dip->tank = $tank;
            }

            return view('edit-closed', [  // Make sure this matches your file location
                'shift' => $shift,
                'tankDips' => $tankDips,
                'nozzleReadings' => $nozzleReadings,
                'cashFlow' => $cashFlow,
                'driverCredits' => $driverCredits
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in editClosedShift: ' . $e->getMessage());
            return redirect('/shifts')->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function updateClosedShift(Request $request, $id)
    {
        $editPermission = $this->canEditClosedShift($id);
        if (!$editPermission['can_edit']) {
            return response()->json(['success' => false, 'message' => $editPermission['message']], 403);
        }

        $validated = $request->validate([
            'end_time' => 'required|date',
            'cash_return' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Convert datetime-local format to MySQL datetime format
            $endTime = Carbon::parse($validated['end_time'])->format('Y-m-d H:i:s');
            $cashReturn = floatval($validated['cash_return']);

            // Get current shift details
            $currentShift = DB::table('shifts')->where('id', $id)->first();

            // Update closed shift
            DB::statement("UPDATE shifts SET 
            end_time = ?, 
            cash_return = ?, 
            is_edited = 1, 
            edited_at = ?, 
            edited_by = ?, 
            updated_at = ? 
            WHERE id = ?", [
                $endTime,
                $cashReturn,
                Carbon::now(),
                auth()->id(),
                Carbon::now(),
                $id
            ]);

            // ✅ CRITICAL: Update next open shift's cash_handover if exists
            $nextOpenShift = DB::table('shifts')
                ->where('station_id', $currentShift->station_id)
                ->where('status', 'open')
                ->where('id', '>', $id)
                ->orderBy('id', 'asc')
                ->first();

            if ($nextOpenShift) {
                // Update next shift's cash_handover with this shift's cash_return
                DB::table('shifts')
                    ->where('id', $nextOpenShift->id)
                    ->update([
                        'cash_handover' => $cashReturn,
                        'updated_at' => Carbon::now()
                    ]);

                \Log::info("Updated open shift {$nextOpenShift->id} cash_handover to: {$cashReturn}");
            }

            // Update tank dips
            if ($request->has('tank_dips') && !empty($request->tank_dips)) {
                foreach ($request->tank_dips as $dip) {
                    $existingDip = DB::table('tanks_dip')
                        ->where('shift_id', $id)
                        ->where('tank_id', $dip['tank_id'])
                        ->first();

                    if ($existingDip) {
                        DB::table('tanks_dip')
                            ->where('shift_id', $id)
                            ->where('tank_id', $dip['tank_id'])
                            ->update([
                                'dip_mm' => floatval($dip['dip_mm']),
                                'dip_in_liters' => floatval($dip['dip_in_liters'])
                            ]);
                    } else {
                        DB::table('tanks_dip')->insert([
                            'shift_id' => $id,
                            'tank_id' => $dip['tank_id'],
                            'dip_mm' => floatval($dip['dip_mm']),
                            'dip_in_liters' => floatval($dip['dip_in_liters']),
                            'created_at' => Carbon::now()
                        ]);
                    }
                }
            }

            // Update nozzle readings
            if ($request->has('nozzle_readings') && !empty($request->nozzle_readings)) {
                foreach ($request->nozzle_readings as $reading) {
                    DB::table('shift_nozzle_readings')
                        ->where('shift_id', $id)
                        ->where('nozzle_id', $reading['nozzle_id'])
                        ->update([
                            'closing_reading' => floatval($reading['closing_reading'])
                        ]);
                }
            }

            // Update cash flow
            if ($request->has('cash_flow') && !empty($request->cash_flow)) {
                $existingCashFlow = DB::table('shift_cash_flow')->where('shift_id', $id)->first();

                $cashFlowData = [
                    'total_cash' => floatval($request->cash_flow['total_cash']),
                    'in_hand' => floatval($request->cash_flow['in_hand']),
                    'in_bank' => floatval($request->cash_flow['in_bank'])
                ];

                if ($existingCashFlow) {
                    DB::table('shift_cash_flow')
                        ->where('shift_id', $id)
                        ->update($cashFlowData);
                } else {
                    $cashFlowData['shift_id'] = $id;
                    $cashFlowData['shift_incharge'] = $request->cash_flow['shift_incharge'] ?? $currentShift->shift_incharger;
                    $cashFlowData['from_date'] = $request->cash_flow['from_date'] ?? $currentShift->start_time;
                    $cashFlowData['to_date'] = $request->cash_flow['to_date'] ?? $endTime;
                    $cashFlowData['created_at'] = Carbon::now();
                    DB::table('shift_cash_flow')->insert($cashFlowData);
                }
            }

            // Update driver credits
            if ($request->has('driver_credits') && !empty($request->driver_credits)) {
                DB::table('credit_driver')->where('shift_id', $id)->delete();

                foreach ($request->driver_credits as $credit) {
                    DB::table('credit_driver')->insert([
                        'shift_id' => $id,
                        'station_id' => $credit['station_id'],
                        'account_id' => $credit['account_id'],
                        'amount_given_to' => $credit['amount_given_to'],
                        'vehicle_number' => $credit['vehicle_number'] ?? null,
                        'cnic' => $credit['cnic'] ?? null,
                        'amount' => floatval($credit['amount']),
                        'created_by' => auth()->id(),
                        'created_at' => Carbon::now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shift updated successfully! Next open shift cash handover has been updated.',
                'next_shift_updated' => $nextOpenShift ? true : false
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating closed shift: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating shift: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getShiftForEdit($id)
    {
        $shift = DB::table('shifts')
            ->join('stations', 'shifts.station_id', '=', 'stations.id')
            ->leftJoin('employees', 'shifts.shift_incharger', '=', 'employees.id')
            ->leftJoin('users', 'employees.user_id', '=', 'users.id')
            ->select('shifts.*', 'stations.name as station_name', 'users.full_name as shift_incharger_name')
            ->where('shifts.id', $id)
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }

        $editPermission = $this->canEditClosedShift($id);
        $shift->can_edit = $editPermission['can_edit'];
        $shift->edit_message = $editPermission['message'];
        $shift->is_edited = isset($shift->is_edited) ? (bool) $shift->is_edited : false;

        $shift->tank_dips = DB::table('tanks_dip')->where('shift_id', $id)->get() ?? [];
        $shift->nozzle_readings = DB::table('shift_nozzle_readings')->where('shift_id', $id)->get() ?? [];
        $shift->cash_flow = DB::table('shift_cash_flow')->where('shift_id', $id)->first();
        $shift->driver_credits = DB::table('credit_driver')->where('shift_id', $id)->get() ?? [];

        return response()->json($shift);
    }
public function getByShiftId($shiftId)
{
    $cashFlow = DB::select("SELECT * FROM shift_cash_flow WHERE shift_id = ?", [$shiftId]);
    return response()->json($cashFlow);
}
}

