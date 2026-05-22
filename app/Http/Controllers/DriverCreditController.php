<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverCreditController extends Controller
{
    // Store driver credit data
    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_data' => 'required|array',
            'driver_data.*.shift_id' => 'required|integer|exists:shifts,id',
            'driver_data.*.station_id' => 'required|integer|exists:stations,id',
            'driver_data.*.account_id' => 'required|integer|exists:accounts,id',
            'driver_data.*.amount_given_to' => 'required|in:Driver,Vehicle',
            'driver_data.*.vehicle_number' => 'required_if:amount_given_to,Vehicle|nullable|string|max:45',
            'driver_data.*.cnic' => [
                'required_if:amount_given_to,Driver',
                'nullable',
                'string',
                'max:13',
                'min:13',
                'regex:/^[0-9]{13}$/'
            ],
            'driver_data.*.amount' => 'required|numeric|min:0',
            'driver_data.*.created_by' => 'required|integer|exists:users,id',
        ]);

        $savedIds = [];
        $existingRecords = [];

        DB::beginTransaction();
        try {
            foreach ($validated['driver_data'] as $data) {
                // ✅ CHECK IF RECORD ALREADY EXISTS
                $existing = DB::table('credit_driver')
                    ->where('shift_id', $data['shift_id'])
                    ->where('amount_given_to', $data['amount_given_to'])
                    ->where(function ($query) use ($data) {
                        if ($data['amount_given_to'] === 'Driver') {
                            $query->where('cnic', $data['cnic']);
                        } else {
                            $query->where('vehicle_number', $data['vehicle_number']);
                        }
                    })
                    ->where('amount', $data['amount'])
                    ->first();

                if ($existing) {
                    $existingRecords[] = $existing->id;
                    continue; // Skip insertion
                }

                $id = DB::table('credit_driver')->insertGetId([
                    'shift_id' => $data['shift_id'],
                    'station_id' => $data['station_id'],
                    'account_id' => $data['account_id'],
                    'amount_given_to' => $data['amount_given_to'],
                    'vehicle_number' => $data['vehicle_number'] ?? null,
                    'cnic' => $data['cnic'] ?? null,
                    'amount' => $data['amount'],
                    'created_by' => $data['created_by'],
                    'created_at' => now()
                ]);

                $savedIds[] = $id;
            }

            DB::commit();

            $message = 'Driver credits saved successfully';
            if (!empty($existingRecords)) {
                $message .= ' (some records already existed)';
            }

            return response()->json([
                'message' => $message,
                'ids' => $savedIds,
                'existing_ids' => $existingRecords
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving driver credits', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return response()->json([
                'message' => 'Error saving driver credits',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    // Get driver credits by shift
    public function getByShift($shiftId)
    {
        $credits = DB::table('credit_driver as cd')
            ->join('stations as s', 'cd.station_id', '=', 's.id')
            ->join('accounts as a', 'cd.account_id', '=', 'a.id')
            ->join('users as u', 'cd.created_by', '=', 'u.id')
            ->where('cd.shift_id', $shiftId)
            ->select(
                'cd.*',
                's.name as station_name',
                'a.name as customer_name',
                'a.phone as customer_phone',
                'u.full_name as created_by_name'
            )
            ->orderBy('cd.created_at', 'desc')
            ->get();

        return response()->json($credits);
    }
	
	
public function getByShift1($shiftId)
{
    $credits = DB::table('credit_driver as cd')
        ->join('stations as s', 'cd.station_id', '=', 's.id')
        ->join('accounts as a', 'cd.account_id', '=', 'a.id')
        ->join('users as u', 'cd.created_by', '=', 'u.id')
        ->where('cd.shift_id', $shiftId)
        ->where('cd.is_paid', 1) 
        ->select(
            'cd.*',
            's.name as station_name',
            'a.name as customer_name',
            'a.phone as customer_phone',
            'u.full_name as created_by_name'
        )
        ->orderBy('cd.created_at', 'desc')
        ->get();

    return response()->json($credits);
}
	public function getAdminData()
    {
        try {
            $credits = DB::select("
                SELECT 
                    cd.id,
                    cd.station_id,
                    cd.amount_given_to,
                    cd.vehicle_number,
                    cd.cnic,
                    cd.amount,
                    cd.is_paid,
                    cd.created_at,
                    s.shift_no
                FROM credit_driver cd
                LEFT JOIN shifts s ON cd.shift_id = s.id
               
                ORDER BY cd.created_at DESC
            ");
            
            return response()->json($credits);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get driver credit data for owner
     */
    public function getOwnerData($userId)
    {
        try {
            $credits = DB::select("
                SELECT 
                    cd.id,
                    cd.station_id,
                    cd.amount_given_to,
                    cd.vehicle_number,
                    cd.cnic,
                    cd.amount,
                    cd.is_paid,
                    cd.created_at,
                    s.shift_no
                FROM credit_driver cd
                LEFT JOIN shifts s ON cd.shift_id = s.id
                INNER JOIN stations st ON cd.station_id = st.id
                WHERE  st.user_id = ?
                ORDER BY cd.created_at DESC
            ", [$userId]);
            
            return response()->json($credits);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get driver credit data for employee
     */
    public function getEmployeeData($userId)
    {
        try {
            $employee = DB::select("
                SELECT station_id FROM employees WHERE user_id = ? LIMIT 1
            ", [$userId]);
            
            if (empty($employee)) {
                return response()->json([]);
            }
            
            $stationId = $employee[0]->station_id;
            
            $credits = DB::select("
                SELECT 
                    cd.id,
                    cd.station_id,
                    cd.amount_given_to,
                    cd.vehicle_number,
                    cd.cnic,
                    cd.amount,
                    cd.is_paid,
                    cd.created_at,
                    s.shift_no
                FROM credit_driver cd
                LEFT JOIN shifts s ON cd.shift_id = s.id
                WHERE cd.station_id = ?
                ORDER BY cd.created_at DESC
            ", [$stationId]);
            
            return response()->json($credits);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Receive driver credit payment
     */
    /**
 * Receive driver credit payment
 */
public function receivePayment(Request $request)
{
    try {
        $request->validate([
            'driver_credit_id' => 'required|integer',
            'station_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'payment_method' => 'required|in:cash,bank',
            'amount' => 'required|numeric|min:0',
            'bank_account_id' => 'required_if:payment_method,bank|integer|exists:accounts,id'
        ]);
        
        $credit = DB::select("
            SELECT id, is_paid, stationrow_id, account_id 
            FROM credit_driver 
            WHERE id = ? 
            LIMIT 1
        ", [$request->driver_credit_id]);
        
        if (empty($credit)) {
            return response()->json(['message' => 'Driver credit record not found'], 404);
        }
        
        if ($credit[0]->is_paid == 1) {
            return response()->json(['message' => 'This credit has already been paid'], 400);
        }
        
        $shift = DB::select("
            SELECT id, shift_no, status 
            FROM shifts 
            WHERE id = ? 
            LIMIT 1
        ", [$request->shift_id]);
        
        if (empty($shift)) {
            return response()->json(['message' => 'Shift not found'], 404);
        }
        
        $shiftData = $shift[0];
        
        DB::beginTransaction();
        
        if ($request->payment_method == 'bank') {
            $bankAccountId = $request->bank_account_id;
            
            $lastRecord = DB::select("
                SELECT amount 
                FROM site_total_ammount 
                WHERE station_id = ? AND account_id = ?
                ORDER BY id DESC 
                LIMIT 1
            ", [$request->station_id, $bankAccountId]);
            
            $previousAmount = !empty($lastRecord) ? $lastRecord[0]->amount : 0;
            $newAmount = $previousAmount + $request->amount;
            
            DB::insert("
                INSERT INTO site_total_ammount (
                    station_id, 
                    account_id, 
                    amount, 
                    previous_amount, 
                    date, 
                    created_by, 
                    created_at, 
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $request->station_id,
                $bankAccountId,
                $newAmount,
                $previousAmount,
                date('Y-m-d H:i:s'),
                auth()->id(),
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
        }
        
        // Update credit_driver with is_paid=1, shift_id, and method
        DB::update("
            UPDATE credit_driver 
            SET is_paid = 1, 
                shift_id = ?, 
                method = ?, 
                updated_at = ? 
            WHERE id = ?
        ", [
            $request->shift_id, 
            $request->payment_method, 
            date('Y-m-d H:i:s'), 
            $request->driver_credit_id
        ]);
        
        DB::commit();
        
        return response()->json([
            'message' => 'Driver credit payment received successfully',
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'shift_no' => $shiftData->shift_no
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed to receive payment: ' . $e->getMessage()], 500);
    }
}
    /**
     * Get open shifts for a station
     */
    public function getOpenShifts($stationId)
    {
        try {
            $shifts = DB::select("
                SELECT id, shift_no, start_time, end_time, status
                FROM shifts
                WHERE station_id = ? AND status = 'open'
                ORDER BY id DESC
            ", [$stationId]);
            
            return response()->json([
                'data' => $shifts
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
/**
 * Get bank accounts for a station
 */
public function getBankAccounts($stationId)
{
    try {
        $accounts = DB::select("
            SELECT id, name, bank_name, account_number 
            FROM accounts 
            WHERE station_id = ? AND type = 'bank'
            ORDER BY name ASC
        ", [$stationId]);
        
        return response()->json([
            'data' => $accounts
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}