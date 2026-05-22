<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SiteTotalAmountController extends Controller
{
    // Get all site total amount records (for admin)
    public function index()
    {
        $records = DB::select(
            'SELECT sta.*, 
                    s.name as station_name,
                    a.name as account_name, 
                    a.account_number,
					a.type,
                    a.bank_name
             FROM site_total_ammount sta
             LEFT JOIN stations s ON sta.station_id = s.id
             LEFT JOIN accounts a ON sta.account_id = a.id OR sta.account_id = a.stationrow_id 
             ORDER BY sta.created_at DESC'
        );

        return response()->json($records);
    }

    // Get records for specific user (owner)
    public function getByUser($userId)
    {
        $records = DB::select(
            'SELECT sta.*, 
                    s.name as station_name,
                    a.name as account_name, 
                    a.account_number,
					a.type,

                    a.bank_name
             FROM site_total_ammount sta
             LEFT JOIN stations s ON sta.station_id = s.id
             LEFT JOIN accounts a ON sta.account_id = a.id OR sta.account_id = a.stationrow_id
             WHERE s.user_id = ?
             ORDER BY sta.created_at DESC',
            [$userId]
        );

        return response()->json($records);
    }

// Get records for employee
public function getByEmployee($userId)
{
    // First get employee record using user_id
    $employee = DB::selectOne(
        'SELECT * FROM employees WHERE user_id = ?',
        [$userId]
    );

    if (!$employee) {
        return response()->json([]);
    }

    // Now get station_id from employee record
    $stationId = $employee->station_id;

    // Get all records for this station
    $records = DB::select(
        'SELECT sta.*, 
                s.name as station_name,
                a.name as account_name, 
                a.account_number,
				a.type,
                a.bank_name
         FROM site_total_ammount sta
         LEFT JOIN stations s ON sta.station_id = s.id
         LEFT JOIN accounts a ON sta.account_id = a.id OR sta.account_id = a.stationrow_id
         WHERE sta.station_id = ?
         ORDER BY sta.created_at DESC',
        [$stationId]
    );

    return response()->json($records);
}

    // Get latest amount for station-account combination
    public function getLatestAmount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|integer',
            'account_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the latest record for this station-account combination
        $latestRecord = DB::selectOne(
            'SELECT amount as previous_amount 
             FROM site_total_ammount 
             WHERE station_id = ? AND account_id = ? 
             ORDER BY created_at DESC 
             LIMIT 1',
            [$request->station_id, $request->account_id]
        );

        return response()->json([
            'previous_amount' => $latestRecord ? $latestRecord->previous_amount : 0
        ]);
    }

    // ✅ CORRECTED: Create new site total amount record
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|integer|exists:stations,id',
            'account_id' => 'required|integer|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'previous_amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // ✅ CORRECT: Total amount = previous_amount + new amount
            $totalAmount = $request->previous_amount + $request->amount;
            
            // Insert new record
            DB::insert(
                'INSERT INTO site_total_ammount (
                    station_id, account_id, amount, previous_amount, date, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())',
                [
                    $request->station_id,
                    $request->account_id,
                    $totalAmount, // ✅ TOTAL amount save kare
                    $request->previous_amount,
                    $request->date
                ]
            );

            $recordId = DB::getPdo()->lastInsertId();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Amount recorded successfully',
                'record_id' => $recordId,
                'total_amount' => $totalAmount
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save site total amount: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save amount',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get summary by station
    public function getSummaryByStation($stationId)
    {
        $stationrec=DB::select("select * from stations where id=?",[$stationId]);
        if($stationrec[0]->local =="1"){
            $summary = DB::select(
            'SELECT a.name as account_name, 
                    a.account_number,
                    a.bank_name,
                    SUM(sta.amount) as total_amount,
                    MAX(sta.created_at) as last_updated
             FROM site_total_ammount sta
             LEFT JOIN accounts a ON sta.account_id = a.stationrow_id
             WHERE sta.station_id = ?
             GROUP BY sta.account_id, a.name, a.account_number, a.bank_name
             ORDER BY total_amount DESC',
            [$stationId]
        );
        }else{
        $summary = DB::select(
            'SELECT a.name as account_name, 
                    a.account_number,
                    a.bank_name,
                    SUM(sta.amount) as total_amount,
                    MAX(sta.created_at) as last_updated
             FROM site_total_ammount sta
             LEFT JOIN accounts a ON sta.account_id = a.id
             WHERE sta.station_id = ?
             GROUP BY sta.account_id, a.name, a.account_number, a.bank_name
             ORDER BY total_amount DESC',
            [$stationId]
        );
}
        return response()->json($summary);
    }

    // Get total amount for all stations (admin only)
    public function getTotalSummary()
    {
        $summary = DB::select(
            'SELECT s.name as station_name,
                    s.id as station_id,
                    COUNT(DISTINCT sta.account_id) as total_accounts,
                    SUM(sta.amount) as total_amount,
                    MAX(sta.created_at) as last_updated
             FROM site_total_ammount sta
             LEFT JOIN stations s ON sta.station_id = s.id
             GROUP BY sta.station_id, s.name, s.id
             ORDER BY total_amount DESC'
        );

        return response()->json($summary);
    }

    // ✅ NEW: Get bank accounts for station
    public function getBankAccountsByStation($stationId)
    {
        $accounts = DB::select(
            'SELECT a.*, s.id as station_id, s.name as station_name 
             FROM accounts a 
             JOIN stations s ON a.station_id = s.id 
             WHERE a.type = ? AND a.station_id = ?',
            ['bank', $stationId]
        );

        if (empty($accounts)) {
            return response()->json(['message' => 'No bank accounts found for this station'], 404);
        }

        return response()->json($accounts);
    }
}