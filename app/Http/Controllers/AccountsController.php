<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountsController extends Controller
{
    // Get all accounts
    public function index()
    {
        $accounts = DB::select('SELECT a.*, s.id as station_id, s.name as station_name
         FROM accounts a 
         join stations s on a.station_id = s.id
         ORDER BY created_at DESC');
        return response()->json($accounts);
    }

    public function index1($user_id)
    {
        $accounts = DB::select('SELECT a.*, s.user_id as stations_user_id, s.name as station_name
        FROM accounts a
        LEFT JOIN stations s on a.station_id = s.id
        WHERE s.user_id = ?
        ORDER by a.created_at DESC
        ', [$user_id]);
        return response()->json($accounts);
    }


    // Get a single account by ID
    public function show($id)
    {
        $account = DB::select('SELECT * FROM accounts WHERE id = ?', [$id]);

        if (empty($account)) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        return response()->json($account[0]);
    }

    // Create a new account
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required',
            'name' => 'required|string|max:244',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cnic' => 'required|string|max:255',
            'station_id' => 'required|integer',
            'coords' => 'required_if:type,supplier|string|max:255|nullable',
            'address' => 'required_if:type,supplier|string|max:255|nullable',
            'bank_name' => 'required_if:type,bank|string|max:255|nullable',
            'account_number' => 'required_if:type,bank|string|max:255|nullable',
            'mdr' => 'required_if:type,bank|string|max:255|nullable',

        ]);

        // $validatedData = $request;
        DB::insert(
            'INSERT INTO accounts (type, name, phone, email, coords, cnic, address,bank_name,account_number,mdr, station_id, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?,?, NOW(), NOW())',
            [
                $validatedData['type'],
                $validatedData['name'],
                $validatedData['phone'],
                $validatedData['email'],
                $validatedData['coords'],
                $validatedData['cnic'],
                $validatedData['address'],
                $validatedData['bank_name'],
                $validatedData['account_number'],
                $validatedData['mdr'],
                $validatedData['station_id'],
            ]
        );

        return response()->json(['message' => 'Account created successfully'], 201);
    }

    // Update an existing account
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'type' => 'required',
            'name' => 'sometimes|required|string|max:244',
            'phone' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'coords' => 'sometimes|nullable|string|max:255',
            'cnic' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'bank_name' => 'sometimes|nullable|string|max:255',
            'account_number' => 'sometimes|nullable|string|max:255',
            'mdr' => 'sometimes|nullable|string|max:255',
            'station_id' => 'sometimes|required|integer',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE accounts SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Account updated successfully']);
    }

    // Delete an account
    public function destroy($id)
    {
        $deleted = DB::delete('DELETE FROM accounts WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'Account deleted successfully']);
        }

        return response()->json(['message' => 'Account not found'], 404);
    }

    // Get accounts by station
    public function getAccountsByStation($stationId)
    {
        $accounts = DB::select('SELECT a.*, s.id as station_id , s.name as station_name FROM accounts a JOIN stations s on a.station_id = s.id WHERE station_id = ?
', [$stationId]);

        if (empty($accounts)) {
            return response()->json(['message' => 'No accounts found for this station'], 404);
        }

        return response()->json($accounts);
    }

    public function getAccountsByStationbank($stationId)
    {
        $accounts = DB::select('
        SELECT 
            a.*,
            s.id as station_id,
            s.name as station_name,
            sta.amount as current_amount,
            sta.previous_amount,
            sta.date as last_transaction_date
        FROM accounts a 
        LEFT JOIN stations s ON a.station_id = s.id 
        LEFT JOIN (
            -- Subquery to get latest record per account
            SELECT st1.*
            FROM site_total_ammount st1
            INNER JOIN (
                SELECT account_id, MAX(created_at) as max_created
                FROM site_total_ammount
                GROUP BY account_id
            ) st2 ON st1.account_id = st2.account_id AND st1.created_at = st2.max_created
        ) sta ON a.id = sta.account_id
        WHERE a.station_id = ? 
            AND a.type = "bank"
        ORDER BY a.name ASC
    ', [$stationId]);

        if (empty($accounts)) {
            return response()->json(['message' => 'No bank accounts found for this station'], 404);
        }

        return response()->json($accounts);
    }

    // Get accounts by category (type)
    public function getAccountsByCategory($type)
    {
        $accounts = DB::select('SELECT * FROM accounts WHERE type = ?', [$type]);

        if (empty($accounts)) {
            return response()->json(['message' => 'No accounts found for this category'], 404);
        }

        return response()->json($accounts);
    }

    public function getAccountsByCategory1($type, $user_id)
    {
        // If userId is provided → filter accounts based on user's stations
        if ($user_id) {
            $accounts = DB::select(
                'SELECT a.*
             FROM accounts a
             JOIN stations s ON a.station_id = s.id
             WHERE a.type = ? AND s.user_id = ?',
                [$type, $user_id]
            );
        } else {
            // fallback (old behavior)
            $accounts = DB::select('SELECT * FROM accounts WHERE type = ?', [$type]);
        }

        if (empty($accounts)) {
            return response()->json(['message' => 'No accounts found for this category'], 404);
        }

        return response()->json($accounts);
    }

    // Get accounts by station ID and type
    public function getAccountsByStationAndType($stationId, $type)
    {
        $accounts = DB::select(
            'SELECT a.*
         FROM accounts a
         WHERE a.type = ? AND a.station_id = ?',
            [$type, $stationId]
        );

        if (empty($accounts)) {
            return response()->json(['message' => 'No accounts found for this station and category'], 404);
        }

        return response()->json($accounts);
    }

    /**
     * Get accounts by station ID and type (fuelcard, creditcard, bank)
     */
    public function getAccountsByStationAndType1($stationId, $type)
    {
        $validTypes = ['bank', 'fuelcard', 'creditcard', 'cash', 'supplier', 'customer', 'extras'];

        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid account type'], 400);
        }

        $accounts = DB::select('
        SELECT a.*, s.name as station_name
        FROM accounts a
        LEFT JOIN stations s ON a.station_id = s.id
        WHERE a.station_id = ? AND a.type = ?
        ORDER BY a.name ASC
    ', [$stationId, $type]);

        if (empty($accounts)) {
            return response()->json([], 200);
        }

        return response()->json($accounts);
    }

    public function sync(Request $request)
    {
        $logs = $request->logs;


        DB::beginTransaction();

        try {

            foreach ($logs as $log) {

                $table = $log['table_name'];
                $action = $log['action'];



                // 👉 Parse JSON here (SERVER SIDE ONLY)
                $data = $log['data'] ? json_decode($log['data'], true) : [];
                $stationId = $data['station_id'];
                $localId = $data['id'] ?? $log['record_id'];

                if (!$localId)
                    continue;

                if ($action == 'insert') {

                    unset($data['id']);

                    $data['station_id'] = $stationId;
                    $data['stationrow_id'] = $localId;
                    $data['created_at'] = now();
                    $data['updated_at'] = now();

                    DB::table($table)->insert($data);

                } elseif ($action == 'update') {

                    unset($data['id']);

                    $data['updated_at'] = now();

                    DB::table($table)
                        ->where('station_id', $stationId)
                        ->where('stationrow_id', $localId)
                        ->update($data);

                } elseif ($action == 'delete') {

                    DB::table($table)
                        ->where('station_id', $stationId)
                        ->where('stationrow_id', $localId)
                        ->delete();
                }
            }

            DB::commit();

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}