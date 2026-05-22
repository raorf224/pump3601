<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    // Get all transactions
    public function index()
    {
        $transactions = DB::select(
            'SELECT t.id, t.station_id, s.name AS station_name, t.account_id, a.name AS account_name, 
                    t.shift_id, t.type, t.debit, t.credit, t.method, t.to_account, 
                    b.name AS to_account_name, b.bank_name, b.account_number,
                    t.note, t.created_at
             FROM transactions t
             LEFT JOIN stations s ON t.station_id = s.id
             LEFT JOIN accounts a ON t.account_id = a.id OR t.account_id = a.stationrow_id
             LEFT JOIN accounts b ON t.to_account = b.id OR t.to_account = b.stationrow_id
             ORDER BY t.created_at DESC'
        );

        return response()->json($transactions);
    }

    public function index1($user_id)
    {
        $transactions = DB::select(
            'SELECT t.id, t.station_id, s.name AS station_name, t.account_id, a.name AS account_name, 
                    t.shift_id, t.type, t.debit, t.credit, t.method, t.to_account,
                    b.name AS to_account_name, b.bank_name, b.account_number,
                    t.note, t.created_at, s.user_id as station_user_id
             FROM transactions t
             LEFT JOIN stations s ON t.station_id = s.id
             LEFT JOIN accounts a ON t.account_id = a.id OR t.account_id = a.stationrow_id
             LEFT JOIN accounts b ON t.to_account = b.id OR t.to_account = b.stationrow_id
             WHERE s.user_id = ?
             ORDER BY t.created_at DESC',
            [$user_id]
        );

        return response()->json($transactions);
    }

    // ✅ NEW: Get transactions for employee
    public function getByEmployee($user_id)
    {
        // Pehle employee ki station find karein
        $employeeStation = DB::selectOne(
            'SELECT station_id FROM employees WHERE user_id = ?',
            [$user_id]
        );

        if (!$employeeStation) {
            return response()->json(['message' => 'Employee station not found'], 404);
        }

        // Phir us station ki transactions
        $transactions = DB::select(
            'SELECT t.id, t.station_id, s.name AS station_name, t.account_id, a.name AS account_name, 
                    t.shift_id, t.type, t.debit, t.credit, t.method, t.to_account, 
                    b.name AS to_account_name, b.bank_name, b.account_number,
                    t.note, t.created_at
             FROM transactions t
             LEFT JOIN stations s ON t.station_id = s.id
           LEFT JOIN accounts a ON t.account_id = a.id OR t.account_id = a.stationrow_id
             LEFT JOIN accounts b ON t.to_account = b.id OR t.to_account = b.stationrow_id
             WHERE t.station_id = ?
             ORDER BY t.created_at DESC',
            [$employeeStation->station_id]
        );

        return response()->json($transactions);
    }

    // Get a single transaction by ID
    public function show($id)
    {
        $transaction = DB::select(
            'SELECT t.id, t.station_id, s.name AS station_name, t.account_id, a.name AS account_name, 
                    t.shift_id, t.type, t.debit, t.credit, t.method, t.to_account,
                    b.name AS to_account_name, b.bank_name, b.account_number,
                    t.note, t.created_at
             FROM transactions t
             LEFT JOIN stations s ON t.station_id = s.id
             LEFT JOIN accounts a ON t.account_id = a.id OR t.account_id = a.stationrow_id
             LEFT JOIN accounts b ON t.to_account = b.id OR t.to_account = b.stationrow_id
             WHERE t.id = ?',
            [$id]
        );

        if (empty($transaction)) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction[0]);
    }

    // Create a new transaction
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'station_id' => 'required|integer|exists:stations,id',
            'shift_id' => 'nullable|integer|exists:shifts,id',
            'account_id' => 'nullable',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,bank,card,credit',
            'to_account' => 'nullable|integer|exists:accounts,id',
            'note' => 'nullable|string|max:255',
        ]);

        // Start transaction for data consistency
        DB::beginTransaction();

        try {
            // Decide debit/credit based on type
            $debit = 0;
            $credit = 0;

            if ($validatedData['type'] === 'income') {
                $credit = $validatedData['amount'];
            } elseif ($validatedData['type'] === 'expense') {
                $debit = $validatedData['amount'];
            }

            // Insert transaction
            DB::insert(
                'INSERT INTO transactions (station_id, shift_id, account_id, type, debit, credit, method, to_account, note, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                [
                    $validatedData['station_id'],
                    $validatedData['shift_id'] ?? null,
                    $validatedData['account_id'],
                    $validatedData['type'],
                    $debit,
                    $credit,
                    $validatedData['method'],
                    $validatedData['to_account'] ?? null,
                    $validatedData['note'] ?? null,
                ]
            );

            $transactionId = DB::getPdo()->lastInsertId();

            // ✅ NEW: If method is bank, update site_total_ammount
            if ($validatedData['method'] === 'bank' && $validatedData['to_account']) {
                $this->updateSiteTotalAmount(
                    $validatedData['station_id'],
                    $validatedData['to_account'],
                    $validatedData['amount'],
                    $validatedData['type'],
                    date('Y-m-d H:i:s')
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction_id' => $transactionId
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update a transaction
    public function update(Request $request, $id)
    {
        // Check if transaction exists
        $transaction = DB::selectOne('SELECT * FROM transactions WHERE id = ?', [$id]);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $validatedData = $request->validate([
            'station_id' => 'required|integer|exists:stations,id',
            'shift_id' => 'nullable|integer|exists:shifts,id',
            'account_id' => 'required|integer|exists:accounts,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,bank,card,credit',
            'to_account' => 'nullable|integer|exists:accounts,id',
            'note' => 'nullable|string|max:255',
        ]);

        // Start transaction for data consistency
        DB::beginTransaction();

        try {
            // Decide debit/credit based on type
            $debit = 0;
            $credit = 0;

            if ($validatedData['type'] === 'income') {
                $credit = $validatedData['amount'];
            } elseif ($validatedData['type'] === 'expense') {
                $debit = $validatedData['amount'];
            }

            // ✅ FIRST: Revert old bank transaction if it was bank
            if ($transaction->method === 'bank' && $transaction->to_account) {
                // Revert the old amount (opposite of original type)
                $revertType = ($transaction->type === 'income') ? 'expense' : 'income';
                $this->updateSiteTotalAmount(
                    $transaction->station_id,
                    $transaction->to_account,
                    ($transaction->type === 'income') ? $transaction->credit : $transaction->debit,
                    $revertType,
                    date('Y-m-d H:i:s')
                );
            }

            // Update transaction
            DB::update(
                'UPDATE transactions 
                 SET station_id = ?, shift_id = ?, account_id = ?, type = ?, 
                     debit = ?, credit = ?, method = ?, to_account = ?, note = ?, updated_at = NOW()
                 WHERE id = ?',
                [
                    $validatedData['station_id'],
                    $validatedData['shift_id'] ?? null,
                    $validatedData['account_id'],
                    $validatedData['type'],
                    $debit,
                    $credit,
                    $validatedData['method'],
                    $validatedData['to_account'] ?? null,
                    $validatedData['note'] ?? null,
                    $id
                ]
            );

            // ✅ SECOND: Apply new bank transaction if method is bank
            if ($validatedData['method'] === 'bank' && $validatedData['to_account']) {
                $this->updateSiteTotalAmount(
                    $validatedData['station_id'],
                    $validatedData['to_account'],
                    $validatedData['amount'],
                    $validatedData['type'],
                    date('Y-m-d H:i:s')
                );
            }

            DB::commit();

            return response()->json(['message' => 'Transaction updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a transaction
    public function destroy($id)
    {
        $transaction = DB::selectOne('SELECT * FROM transactions WHERE id = ?', [$id]);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // ✅ If method was bank, revert the amount
            if ($transaction->method === 'bank' && $transaction->to_account) {
                // Revert the amount (opposite of original type)
                $revertType = ($transaction->type === 'income') ? 'expense' : 'income';
                $amountToRevert = ($transaction->type === 'income') ? $transaction->credit : $transaction->debit;

                $this->updateSiteTotalAmount(
                    $transaction->station_id,
                    $transaction->to_account,
                    $amountToRevert,
                    $revertType,
                    date('Y-m-d H:i:s')
                );
            }

            // Delete transaction
            DB::delete('DELETE FROM transactions WHERE id = ?', [$id]);

            DB::commit();

            return response()->json(['message' => 'Transaction deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function expenseSheet(Request $request)
    {
        $stationId = $request->get('station_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $userId = $request->get('user_id'); // ✅ ADD user_id filter

        $query = "SELECT 
                t.id,
                t.type,
                t.debit AS total_expense,
                t.credit AS total_income,
                t.note,
                t.method,
                t.is_testing,
                t.shift_id,
                t.account_id,
                stations.name AS station_name,
                stations.id AS station_id,
                stations.user_id AS station_user_id,
                (t.credit - t.debit) AS net_balance
            FROM transactions t
            JOIN stations ON t.station_id = stations.id
            WHERE 1=1";

        $params = [];

        // ✅ ROLE BASED FILTERING
        // Agar user_id diya hai aur user admin nahi hai toh sirf uski stations dikhao
        if ($userId && $userId !== '') {
            $query .= " AND stations.user_id = ?";
            $params[] = $userId;
        }

        // Station filter
        if ($stationId && $stationId !== '' && $stationId !== 'null') {
            $query .= " AND t.station_id = ?";
            $params[] = $stationId;
        }

        // Date filter
        if ($startDate && $startDate !== '' && $endDate && $endDate !== '') {
            $query .= " AND DATE(t.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $query .= " ORDER BY t.created_at DESC";

        \Log::info('Expense Sheet Query:', ['query' => $query, 'params' => $params, 'user_id' => $userId]);

        $expenseSheet = DB::select($query, $params);

        return response()->json($expenseSheet);
    }


    public function expenseSheet1(Request $request, $user_id)
    {
        $stationId = $request->get('station_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = "SELECT 
                t.id,
                t.type,
                t.debit AS total_expense,
                t.credit AS total_income,
                t.note,
                t.method,
                t.is_testing,
                t.shift_id,
                t.account_id,
                stations.name AS station_name,
                stations.id AS station_id,
                (t.credit - t.debit) AS net_balance
            FROM transactions t
            JOIN stations ON t.station_id = stations.id
            WHERE 1=1";

        $params = [];

        if ($stationId && $stationId !== '') {
            $query .= " AND t.station_id = ?";
            $params[] = $stationId;
        }

        if ($startDate && $endDate) {
            $query .= " AND DATE(t.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $query .= " ORDER BY t.created_at DESC";

        $expenseSheet = DB::select($query, $params);

        return response()->json($expenseSheet);
    }

    // Get accounts view
    public function accountsView()
    {
        $accounts = DB::select(
            'SELECT 
            a.id AS account_id,
            a.name AS account_name,
            a.type AS account_type,
            s.id AS station_id,
            s.name AS station_name,
            s.user_id AS station_user_id,
            SUM(CASE WHEN t.type = "income" THEN t.credit ELSE 0 END) AS total_income,
            SUM(CASE WHEN t.type = "expense" THEN t.debit ELSE 0 END) AS total_expense,
            (
                SUM(CASE WHEN t.type = "income" THEN t.credit ELSE 0 END) -
                SUM(CASE WHEN t.type = "expense" THEN t.debit ELSE 0 END)
            ) AS net_balance
        FROM accounts a
        LEFT JOIN transactions t ON a.id = t.account_id OR a.stationrow_id = t.account_id
        LEFT JOIN stations s ON a.station_id = s.id
        GROUP BY 
            a.id, a.name, a.type, s.id, s.name, s.user_id
        ORDER BY a.type, a.name'
        );

        return response()->json($accounts);
    }

    public function accountsView1($user_id)
    {
        $accounts = DB::select(
            'SELECT 
            a.id AS account_id,
            a.name AS account_name,
            a.type AS account_type,
            s.id AS station_id, 
            s.name AS station_name, 
            s.user_id AS station_user_id,
            SUM(CASE WHEN t.type = "income" THEN t.credit ELSE 0 END) AS total_income,
            SUM(CASE WHEN t.type = "expense" THEN t.debit ELSE 0 END) AS total_expense,
            (SUM(CASE WHEN t.type = "income" THEN t.credit ELSE 0 END) - 
             SUM(CASE WHEN t.type = "expense" THEN t.debit ELSE 0 END)) AS net_balance
         FROM accounts a
         LEFT JOIN transactions t ON a.id = t.account_id OR a.stationrow_id = t.account_id
         LEFT JOIN stations s ON t.station_id = s.id
         WHERE s.user_id = ?
         GROUP BY 
            a.id, a.name, a.type, 
            s.id, s.name, s.user_id
         ORDER BY a.type, a.name
        ',
            [$user_id]
        );

        return response()->json($accounts);
    }

    public function show_emp($user_id)
    {
        $station = DB::table('employees')
            ->join('stations', 'employees.station_id', '=', 'stations.id')
            ->where('employees.user_id', $user_id)
            ->select(
                'stations.id as station_id',
                'stations.name as station_name',
                'stations.user_id as owner_user_id'
            )
            ->first();

        if (!$station) {
            return response()->json(['message' => 'No assigned station found'], 404);
        }

        return response()->json($station);
    }

    // Get transactions by shift ID
    public function getCashByShift($shiftId)
    {
        $transactions = DB::select(
            'SELECT t.id, t.station_id, s.name AS station_name, t.account_id, a.name AS account_name, 
                    t.shift_id, t.type, t.debit, t.credit, t.method, t.to_account,
                    b.name AS to_account_name, b.bank_name, b.account_number,
                    t.note, t.created_at
             FROM transactions t
             LEFT JOIN stations s ON t.station_id = s.id
             LEFT JOIN accounts a ON t.account_id = a.id OR t.account_id = a.stationrow_id
             LEFT JOIN accounts b ON t.to_account = b.id OR t.to_account = b.stationrow_id
             WHERE t.shift_id = ? AND t.method = "cash"
             ORDER BY t.created_at DESC',
            [$shiftId]
        );

        return response()->json($transactions);
    }

    public function getCashByShift1($shiftId)
    {
        $transactions = DB::select(
            'SELECT t.id, t.station_id, s.name AS station_name, t.account_id, a.name AS account_name, 
        t.shift_id, t.type, t.debit, t.credit, t.method, t.to_account,
        b.name AS to_account_name, b.bank_name, b.account_number,
        t.note, t.created_at
 FROM transactions t
 LEFT JOIN stations s ON t.station_id = s.id
 LEFT JOIN accounts a ON t.account_id = a.id OR t.account_id = a.stationrow_id
 LEFT JOIN accounts b ON t.to_account = b.id OR t.to_account = b.stationrow_id
 WHERE t.shift_id = ?
   AND t.method = "cash"
   AND t.is_testing != 1
 ORDER BY t.created_at DESC;',
            [$shiftId]
        );

        return response()->json($transactions);
    }


    // ✅ NEW: Helper function to update site_total_ammount
    private function updateSiteTotalAmount($stationId, $accountId, $amount, $type, $date = null)
    {
        // Get the latest amount for this station-account combination
        $latestRecord = DB::selectOne(
            'SELECT amount as previous_amount 
             FROM site_total_ammount 
             WHERE station_id = ? AND account_id = ? 
             ORDER BY created_at DESC 
             LIMIT 1',
            [$stationId, $accountId]
        );

        $previousAmount = $latestRecord ? $latestRecord->previous_amount : 0;

        // Calculate new amount based on type
        if ($type === 'income') {
            $newAmount = $previousAmount + $amount;
        } else {
            $newAmount = $previousAmount - $amount;
            // Ensure amount doesn't go negative (optional)
            if ($newAmount < 0) {
                $newAmount = 0;
            }
        }

        // Insert new record
        DB::insert(
            'INSERT INTO site_total_ammount (station_id, account_id, amount, previous_amount, date, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [
                $stationId,
                $accountId,
                $newAmount,
                $previousAmount,
                $date ?: date('Y-m-d H:i:s')
            ]
        );

        return $newAmount;
    }

    // Receive transaction (is_testing = 1 to is_testing = 2)
    public function receiveTransaction(Request $request, $id)
    {
        $validatedData = $request->validate([
            'shift_id' => 'required|integer|exists:shifts,id',
            'account_id' => 'nullable|integer|exists:accounts,id',
            'method' => 'required|in:cash,bank',
        ]);

        // Get the transaction
        $transaction = DB::selectOne('SELECT * FROM transactions WHERE id = ?', [$id]);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Check if already received
        if ($transaction->is_testing == 2) {
            return response()->json(['message' => 'Transaction already received'], 400);
        }

        // Check if is_testing = 1
        if ($transaction->is_testing != 1) {
            return response()->json(['message' => 'This transaction cannot be received'], 400);
        }

        // For bank method, account_id is required
        if ($validatedData['method'] === 'bank' && empty($validatedData['account_id'])) {
            return response()->json(['message' => 'Bank account is required for bank transactions'], 422);
        }

        DB::beginTransaction();

        try {
            $amount = $transaction->type === 'expense' ? $transaction->debit : $transaction->credit;

            // ✅ CHECK METHOD
            if ($validatedData['method'] === 'bank') {
                // ✅ BANK METHOD - Update transaction with bank account
                DB::update(
                    'UPDATE transactions 
                 SET type = "income",
                     debit = 0,
                     credit = ?,
                     shift_id = ?,
                     method = ?,
                     account_id = ?,
                     to_account = NULL,
                     is_testing = 2,
                     updated_at = NOW()
                 WHERE id = ?',
                    [
                        $amount,
                        $validatedData['shift_id'],
                        'bank',
                        $validatedData['account_id'],
                        $id
                    ]
                );

                // ✅ Update site_total_ammount (bank balance)
                // Get current balance
                $currentRecord = DB::selectOne(
                    'SELECT amount FROM site_total_ammount 
                 WHERE station_id = ? AND account_id = ? 
                 ORDER BY id DESC 
                 LIMIT 1',
                    [$transaction->station_id, $validatedData['account_id']]
                );

                $previousAmount = $currentRecord ? floatval($currentRecord->amount) : 0;
                $newAmount = $previousAmount + $amount;

                // Insert new record in site_total_ammount
                DB::insert(
                    'INSERT INTO site_total_ammount (station_id, account_id, amount, previous_amount, date, created_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())',
                    [
                        $transaction->station_id,
                        $validatedData['account_id'],
                        $newAmount,
                        $previousAmount
                    ]
                );

                \Log::info('Bank Transaction Received:', [
                    'transaction_id' => $id,
                    'station_id' => $transaction->station_id,
                    'account_id' => $validatedData['account_id'],
                    'amount' => $amount,
                    'previous_balance' => $previousAmount,
                    'new_balance' => $newAmount
                ]);

            } else {
                // ✅ CASH METHOD - Simple update without bank account
                DB::update(
                    'UPDATE transactions 
                 SET type = "income",
                     debit = 0,
                     credit = ?,
                     shift_id = ?,
                     method = ?,
                     account_id = NULL,
                     to_account = NULL,
                     is_testing = 2,
                     updated_at = NOW()
                 WHERE id = ?',
                    [
                        $amount,
                        $validatedData['shift_id'],
                        'cash',
                        $id
                    ]
                );

                \Log::info('Cash Transaction Received:', [
                    'transaction_id' => $id,
                    'station_id' => $transaction->station_id,
                    'amount' => $amount
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction received successfully',
                'transaction_id' => $id,
                'method' => $validatedData['method'],
                'amount' => $amount,
                'account_id' => $validatedData['method'] === 'bank' ? $validatedData['account_id'] : null,
                'shift_id' => $validatedData['shift_id']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Receive Transaction Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to receive transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}