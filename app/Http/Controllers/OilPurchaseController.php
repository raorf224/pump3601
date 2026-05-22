<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OilPurchaseController extends Controller
{
    // Get all oil purchases with full details
    public function index()
    {
        $oilPurchases = DB::select(
            'SELECT op.id, op.order_date, op.recieving_date, op.payment_status, 
                op.recieved_qty, op.recive_status, op.rate, op.qty, op.invoice_no, op.ref_num, 
                op.stock_update, op.created_at, op.updated_at,
                op.supplier_id, op.station_id, op.shift_id, op.product_id,
                a.name AS supplier_name,
                s.name AS station_name,
                sh.shift_no,
                p.name AS product_name,
                COALESCE(
    (SELECT SUM(shortage) 
     FROM oil_recived_tanks 
     WHERE oil_purchase_id = op.id),
    0
) as total_shortage_sum,

CASE 
    WHEN EXISTS (
        SELECT 1 FROM shortage_ammount_paid_back 
        WHERE oil_purchase_id = op.id AND is_paid = 1
    ) THEN 1
    ELSE 0
END as shortage_paid
         FROM oil_purchase op
         LEFT JOIN accounts a ON op.supplier_id = a.id OR op.supplier_id = a.stationrow_id
         LEFT JOIN stations s ON op.station_id = s.id 
         LEFT JOIN shifts sh ON op.shift_id = sh.id OR op.shift_id = sh.stationrow_id
         LEFT JOIN products p ON op.product_id = p.id  
         ORDER BY op.created_at DESC'
        );

        return response()->json($oilPurchases);
    }

    // Get purchases for specific user
    public function index1($user_id)
    {
        $oilPurchases = DB::select(
            'SELECT op.id, op.order_date, op.recieving_date, op.payment_status, 
            op.recieved_qty, op.recive_status, op.rate, op.qty, op.invoice_no, op.ref_num, 
            op.stock_update, op.created_at, op.updated_at,
            op.supplier_id, op.station_id, op.shift_id, op.product_id,
            a.name AS supplier_name,
            s.name AS station_name, s.user_id as station_user_id,
            sh.shift_no,
            p.name AS product_name,
            COALESCE(
    (SELECT SUM(shortage) 
     FROM oil_recived_tanks 
     WHERE oil_purchase_id = op.id),
    0
) as total_shortage_sum,

-- ✅ Check if shortage payment is made
CASE 
    WHEN EXISTS (
        SELECT 1 FROM shortage_ammount_paid_back 
        WHERE oil_purchase_id = op.id AND is_paid = 1
    ) THEN 1
    ELSE 0
END as shortage_paid
         FROM oil_purchase op
         LEFT JOIN accounts a ON op.supplier_id = a.id OR op.supplier_id = a.stationrow_id
         LEFT JOIN stations s ON op.station_id = s.id 
         LEFT JOIN shifts sh ON op.shift_id = sh.id OR op.shift_id = sh.stationrow_id
         LEFT JOIN products p ON op.product_id = p.id 
         WHERE s.user_id = ?
         ORDER BY op.created_at DESC',
            [$user_id]
        );

        return response()->json($oilPurchases);
    }


    // Get purchase by ID
    public function getbyId($id)
    {
        $oilPurchases = DB::select(
            'SELECT op.id, op.supplier_id, op.station_id, op.shift_id, op.product_id,
        op.order_date, op.recieving_date, op.payment_status, 
        op.recieved_qty, op.rate, op.qty, op.invoice_no, op.ref_num, 
        op.recieved_qty, op.recive_status,  
        op.stock_update, op.created_at, op.updated_at,
        a.name AS supplier_name,
        s.name AS station_name,
        sh.shift_no,
        p.name AS product_name,
        COALESCE(
    (SELECT SUM(shortage) 
     FROM oil_recived_tanks 
     WHERE oil_purchase_id = op.id),
    0
) as total_shortage_sum,

-- ✅ Check if shortage payment is made
CASE 
    WHEN EXISTS (
        SELECT 1 FROM shortage_ammount_paid_back 
        WHERE oil_purchase_id = op.id AND is_paid = 1
    ) THEN 1
    ELSE 0
END as shortage_paid
     FROM oil_purchase op
        LEFT JOIN accounts a ON op.supplier_id = a.id OR op.supplier_id = a.stationrow_id
         LEFT JOIN stations s ON op.station_id = s.id 
         LEFT JOIN shifts sh ON op.shift_id = sh.id OR op.shift_id = sh.stationrow_id
     LEFT JOIN products p ON op.product_id = p.id 
     WHERE op.id = ?',
            [$id]
        );

        return response()->json($oilPurchases);
    }

    // Get purchases by station
    public function getByStation($stationId)
    {
        $stationrec = DB::select('select * from stations where id=?', [$stationId]);
        if ($stationrec[0]->local == "1") {
            $oilPurchases = DB::select(
                'SELECT op.id, op.order_date, op.recieving_date, op.payment_status, 
            op.recieved_qty, op.recive_status, op.rate, op.qty, op.invoice_no, op.ref_num, 
            op.stock_update, op.created_at, op.updated_at,
            op.supplier_id, op.station_id, op.shift_id, op.product_id,
            a.name AS supplier_name,
            s.name AS station_name,
            sh.shift_no,
            p.name AS product_name,
            COALESCE(
    (SELECT SUM(shortage) 
     FROM oil_recived_tanks 
     WHERE oil_purchase_id = op.id),
    0
) as total_shortage_sum,

-- ✅ Check if shortage payment is made
CASE 
    WHEN EXISTS (
        SELECT 1 FROM shortage_ammount_paid_back 
        WHERE oil_purchase_id = op.id AND is_paid = 1
    ) THEN 1
    ELSE 0
END as shortage_paid
         FROM oil_purchase op
         LEFT JOIN accounts a ON op.supplier_id = a.stationrow_id
         LEFT JOIN stations s ON op.station_id = s.id
         LEFT JOIN shifts sh ON op.shift_id = sh.stationrow_id
         LEFT JOIN products p ON op.product_id = p.id  
         WHERE op.station_id = ?
         ORDER BY op.created_at DESC',
                [$stationId]
            );
        } else {
            $oilPurchases = DB::select(
                'SELECT op.id, op.order_date, op.recieving_date, op.payment_status, 
            op.recieved_qty, op.recive_status, op.rate, op.qty, op.invoice_no, op.ref_num, 
            op.stock_update, op.created_at, op.updated_at,
            op.supplier_id, op.station_id, op.shift_id, op.product_id,
            a.name AS supplier_name,
            s.name AS station_name,
            sh.shift_no,
            p.name AS product_name,
            COALESCE(
    (SELECT SUM(shortage) 
     FROM oil_recived_tanks 
     WHERE oil_purchase_id = op.id),
    0
) as total_shortage_sum,

-- ✅ Check if shortage payment is made
CASE 
    WHEN EXISTS (
        SELECT 1 FROM shortage_ammount_paid_back 
        WHERE oil_purchase_id = op.id AND is_paid = 1
    ) THEN 1
    ELSE 0
END as shortage_paid
         FROM oil_purchase op
         LEFT JOIN accounts a ON op.supplier_id = a.id
         LEFT JOIN stations s ON op.station_id = s.id
         LEFT JOIN shifts sh ON op.shift_id = sh.id
         LEFT JOIN products p ON op.product_id = p.id  
         WHERE op.station_id = ?
         ORDER BY op.created_at DESC',
                [$stationId]
            );
        }


        return response()->json($oilPurchases);
    }

    // Create a new oil purchase (all fields nullable)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|integer',
            'station_id' => 'nullable|integer',
            'product_id' => 'nullable|integer',

            'shift_id' => 'nullable|integer',
            'order_date' => 'nullable|string|max:45',
            'payment_status' => 'nullable|in:paid,not_paid,partial',
            'rate' => 'nullable|numeric',
            'qty' => 'nullable|numeric',
            'invoice_no' => 'nullable|string|max:45',
            'ref_num' => 'nullable|string|max:45',
            'created_by' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            DB::insert(
                'INSERT INTO oil_purchase (
                supplier_id, station_id, product_id, shift_id, order_date, 
                payment_status, rate, qty, invoice_no, ref_num, 
                recive_status, stock_update, created_by, created_at, updated_at
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                [
                    $request->supplier_id,
                    $request->station_id,
                    $request->product_id,
                    $request->shift_id,
                    $request->order_date,
                    $request->payment_status,
                    $request->rate,
                    $request->qty,
                    $request->invoice_no,
                    $request->ref_num,
                    'Not-Recived', // ✅ Default: Not received
                    0,    // ✅ Stock not updated yet
                    $request->created_by,
                ]
            );

            DB::commit();
            return response()->json([
                'message' => 'Oil purchase order created successfully',
                'purchase_id' => DB::getPdo()->lastInsertId()
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create oil purchase', 'error' => $e->getMessage()], 500);
        }
    }

    // Update payment status
    public function updatePaymentStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:paid,not_paid,partial',
            'shift_id' => 'required|integer|exists:shifts,id',
            'payment_method' => 'nullable|in:bank,cash',
            'account_id' => 'nullable|required_if:payment_method,bank|integer|exists:accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if purchase exists
        $purchase = DB::select('SELECT * FROM oil_purchase WHERE id = ?', [$id]);
        if (empty($purchase)) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $purchase = $purchase[0];
        $totalPurchaseAmount = $purchase->qty * $purchase->rate;

        // ✅ BANK BALANCE CHECK (agar bank se full payment ho)
        if ($request->payment_method === 'bank' && $request->account_id) {
            $currentBalance = DB::selectOne(
                'SELECT amount FROM site_total_ammount 
                 WHERE station_id = ? AND account_id = ?
                 ORDER BY created_at DESC LIMIT 1',
                [$purchase->station_id, $request->account_id]
            );

            $availableBalance = $currentBalance ? $currentBalance->amount : 0;

            if ($availableBalance < $totalPurchaseAmount) {
                return response()->json([
                    'message' => 'Insufficient balance for full payment',
                    'available_balance' => $availableBalance,
                    'required_amount' => $totalPurchaseAmount,
                    'short_by' => $totalPurchaseAmount - $availableBalance
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Update the payment status in oil_purchase table
            DB::update(
                'UPDATE oil_purchase SET 
                payment_status = ?, 
                shift_id = ?,
                updated_at = NOW() 
             WHERE id = ?',
                [
                    $request->payment_status,
                    $request->shift_id,
                    $id
                ]
            );

            // ✅ BANK PAYMENT KE LIYE site_total_ammount UPDATE
            if ($request->payment_method === 'bank' && $request->account_id) {
                $currentSiteAmount = DB::selectOne(
                    'SELECT amount FROM site_total_ammount 
                     WHERE station_id = ? AND account_id = ?
                     ORDER BY created_at DESC LIMIT 1',
                    [$purchase->station_id, $request->account_id]
                );

                $previousAmount = $currentSiteAmount ? $currentSiteAmount->amount : 0;
                $newAmount = $previousAmount - $totalPurchaseAmount;

                DB::insert(
                    'INSERT INTO site_total_ammount 
                    (station_id, account_id, amount, previous_amount, date, created_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())',
                    [
                        $purchase->station_id,
                        $request->account_id,
                        $newAmount,
                        $previousAmount
                    ]
                );
            }

            // If payment_status is 'paid', also insert into ammount_paid table
            if ($request->payment_status === 'paid') {
                // Check if already exists in ammount_paid table
                $existingPayment = DB::select(
                    'SELECT * FROM ammount_paid 
                 WHERE oil_purchase_id = ? AND type = "debit"',
                    [$id]
                );

                // If no existing payment record, insert one
                if (empty($existingPayment)) {
                    $paymentMethod = $request->payment_method ?? 'cash';

                    DB::insert(
                        'INSERT INTO ammount_paid (
                        oil_purchase_id, account_id, shift_id, type, 
                        method, ammount, date, total_ammount, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, NOW())',
                        [
                            $id,
                            ($paymentMethod === 'bank') ? $request->account_id : null,
                            $request->shift_id,
                            'debit', // Always debit
                            $paymentMethod, // bank or cash
                            $totalPurchaseAmount,
                            $totalPurchaseAmount // total_ammount same as ammount for first payment
                        ]
                    );
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Payment status updated successfully',
                'payment_status' => $request->payment_status,
                'total_amount' => $totalPurchaseAmount,
                'bank_balance_updated' => ($request->payment_method === 'bank') ? 'yes' : 'no'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update payment status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Get payment history for a purchase
    public function getPaymentHistory($purchaseId)
    {
        try {
            // First check if purchase exists
            $purchase = DB::select('SELECT * FROM oil_purchase WHERE id = ?', [$purchaseId]);
            if (empty($purchase)) {
                return response()->json([
                    'message' => 'Purchase not found',
                    'payments' => [],
                    'totals' => []
                ]);
            }

            // Get all payments for this purchase
            $payments = DB::select(
                'SELECT ap.*, a.name as account_name, a.bank_name, a.account_number,
                    sh.shift_no, s.name as station_name
             FROM ammount_paid ap
             LEFT JOIN accounts a ON ap.account_id = a.id OR ap.account_id = a.stationrow_id
             LEFT JOIN shifts sh ON ap.shift_id = sh.id OR ap.shift_id = sh.stationrow_id
             LEFT JOIN oil_purchase op ON ap.oil_purchase_id = op.id OR  ap.oil_purchase_id = op.stationrow_id
             LEFT JOIN stations s ON op.station_id = s.id 
             WHERE ap.oil_purchase_id = ? 
             ORDER BY ap.created_at ASC',
                [$purchaseId]
            );

            // If no payments found, return empty arrays
            if (empty($payments)) {
                return response()->json([
                    'payments' => [],
                    'totals' => [
                        'debit_bank' => 0,
                        'debit_cash' => 0,
                        'credit_bank' => 0,
                        'credit_cash' => 0,
                        'total_debit' => 0,
                        'total_credit' => 0,
                        'net_balance' => 0
                    ]
                ]);
            }

            // Calculate totals by type and method
            $totals = [
                'debit_bank' => 0,
                'debit_cash' => 0,
                'credit_bank' => 0,
                'credit_cash' => 0,
                'total_debit' => 0,
                'total_credit' => 0,
                'net_balance' => 0
            ];

            foreach ($payments as $payment) {
                $amount = floatval($payment->ammount);
                $type = strtolower($payment->type);
                $method = strtolower($payment->method);

                if ($type === 'debit') {
                    $totals['total_debit'] += $amount;
                    if ($method === 'bank') {
                        $totals['debit_bank'] += $amount;
                    } else {
                        $totals['debit_cash'] += $amount;
                    }
                } elseif ($type === 'credit') {
                    $totals['total_credit'] += $amount;
                    if ($method === 'bank') {
                        $totals['credit_bank'] += $amount;
                    } else {
                        $totals['credit_cash'] += $amount;
                    }
                }
            }

            // Calculate net balance (debit - credit)
            $totals['net_balance'] = $totals['total_debit'] - $totals['total_credit'];

            return response()->json([
                'payments' => $payments,
                'totals' => $totals
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPaymentHistory: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching payment history',
                'error' => $e->getMessage(),
                'payments' => [],
                'totals' => []
            ], 500);
        }
    }

    // ✅ Process partial payment - WITH BANK BALANCE CHECK
    public function processPartialPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'shift_id' => 'required|integer|exists:shifts,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:bank,cash',
            'payment_date' => 'required|date',
            'account_id' => 'nullable|required_if:payment_method,bank|integer|exists:accounts,id',
            'type' => 'required|in:debit,credit'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // 1. Purchase check karein
        $purchase = DB::select('SELECT * FROM oil_purchase WHERE id = ?', [$id]);
        if (empty($purchase)) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $purchase = $purchase[0];
        $totalPurchaseAmount = $purchase->qty * $purchase->rate;

        // ✅ 2. BANK PAYMENT KE LIYE BALANCE CHECK
        if ($request->payment_method === 'bank') {
            // Get current balance from site_total_ammount
            $currentBalance = DB::selectOne(
                'SELECT amount FROM site_total_ammount 
                 WHERE station_id = ? AND account_id = ?
                 ORDER BY created_at DESC LIMIT 1',
                [$purchase->station_id, $request->account_id]
            );

            $availableBalance = $currentBalance ? $currentBalance->amount : 0;

            // Agar type = 'debit' hai (payment dena hai)
            if ($request->type === 'debit') {
                // Check if sufficient balance available
                if ($availableBalance < $request->payment_amount) {
                    return response()->json([
                        'message' => 'Insufficient balance in bank account',
                        'available_balance' => $availableBalance,
                        'required_amount' => $request->payment_amount,
                        'short_by' => $request->payment_amount - $availableBalance
                    ], 400);
                }
            }
        }

        DB::beginTransaction();
        try {
            // Get existing payments
            $existingPayments = DB::select(
                'SELECT * FROM ammount_paid 
             WHERE oil_purchase_id = ? AND type = "debit"',
                [$id]
            );

            // Calculate total paid so far
            $totalPaid = 0;
            foreach ($existingPayments as $payment) {
                $totalPaid += $payment->ammount;
            }

            // Calculate remaining amount
            $remainingAmount = $totalPurchaseAmount - $totalPaid;

            // Validate new payment amount
            $newPayment = $request->payment_amount;
            if ($newPayment > $remainingAmount) {
                return response()->json([
                    'message' => 'Payment amount exceeds remaining amount',
                    'remaining_amount' => $remainingAmount,
                    'max_allowed' => $remainingAmount
                ], 400);
            }

            // Calculate new total amount for this type/method combination
            $previousTotal = DB::selectOne(
                'SELECT COALESCE(SUM(ammount), 0) as total FROM ammount_paid 
             WHERE oil_purchase_id = ? AND type = ? AND method = ?',
                [$id, $request->type, $request->payment_method]
            )->total;

            $newTotalForMethod = $previousTotal + $newPayment;

            // ✅ 3. BANK PAYMENT KE LIYE site_total_ammount UPDATE
            $newBankBalance = null;
            if ($request->payment_method === 'bank' && $request->account_id) {
                $currentSiteAmount = DB::selectOne(
                    'SELECT amount FROM site_total_ammount 
                     WHERE station_id = ? AND account_id = ?
                     ORDER BY created_at DESC LIMIT 1',
                    [$purchase->station_id, $request->account_id]
                );

                $previousAmount = $currentSiteAmount ? $currentSiteAmount->amount : 0;

                // Calculate new amount based on type
                if ($request->type === 'debit') {
                    // Payment dena hai - amount decrease hoga
                    $newBankBalance = $previousAmount - $newPayment;
                } else {
                    // Credit hai - amount increase hoga
                    $newBankBalance = $previousAmount + $newPayment;
                }

                // Insert new record in site_total_ammount
                DB::insert(
                    'INSERT INTO site_total_ammount 
                    (station_id, account_id, amount, previous_amount, date, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())',
                    [
                        $purchase->station_id,
                        $request->account_id,
                        $newBankBalance,
                        $previousAmount,
                        $request->payment_date
                    ]
                );
            }

            // Insert into ammount_paid table with custom date
            DB::insert(
                'INSERT INTO ammount_paid (
                oil_purchase_id, account_id, shift_id, type, 
                method, ammount, date, total_ammount, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                [
                    $id,
                    ($request->payment_method === 'bank') ? $request->account_id : null,
                    $request->shift_id,
                    $request->type,
                    $request->payment_method,
                    $newPayment,
                    $request->payment_date,
                    $newTotalForMethod
                ]
            );

            // Update total paid
            $newTotalPaid = $totalPaid + $newPayment;

            // Check if fully paid
            if ($newTotalPaid >= $totalPurchaseAmount) {
                // Update purchase status to paid
                DB::update(
                    'UPDATE oil_purchase SET 
                    payment_status = "paid", 
                    shift_id = ?,
                    updated_at = NOW() 
                 WHERE id = ?',
                    [
                        $request->shift_id,
                        $id
                    ]
                );
                $finalStatus = 'paid';
            } else {
                // Keep as partial
                $finalStatus = 'partial';
            }

            DB::commit();
            return response()->json([
                'message' => 'Payment recorded successfully',
                'payment_status' => $finalStatus,
                'amount_paid' => $newPayment,
                'total_paid' => $newTotalPaid,
                'remaining_amount' => $totalPurchaseAmount - $newTotalPaid,
                'bank_balance_updated' => $request->payment_method === 'bank' ? 'yes' : 'no',
                'new_bank_balance' => $newBankBalance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process partial payment: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update an existing oil purchase (all fields nullable)
    public function update(Request $request, $id)
    {
        // Custom validation - all fields are nullable
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|integer',
            'station_id' => 'nullable|integer',
            'shift_id' => 'nullable|integer',
            'product_id' => 'nullable|integer',
            'order_date' => 'nullable|string|max:45',
            'recieving_date' => 'nullable|string|max:45',
            'payment_status' => 'nullable|in:paid,not_paid,partial',
            'recieved_qty' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
            'qty' => 'nullable|numeric',
            'invoice_no' => 'nullable|string|max:45',
            'ref_num' => 'nullable|string|max:45',
            'stock_update' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the oil purchase exists
        $oilPurchase = DB::select('SELECT * FROM oil_purchase WHERE id = ?', [$id]);
        if (empty($oilPurchase)) {
            return response()->json(['message' => 'Oil purchase not found'], 404);
        }

        DB::beginTransaction();

        try {
            $updateFields = [];
            $updateValues = [];

            // Dynamically build the update query
            $fields = [
                'supplier_id',
                'station_id',
                'shift_id',
                'product_id',
                'order_date',
                'recieving_date',
                'payment_status',
                'recieved_qty',
                'rate',
                'qty',
                'invoice_no',
                'ref_num',
                'stock_update'
            ];

            foreach ($fields as $field) {
                if ($request->has($field)) {
                    $updateFields[] = "$field = ?";
                    $updateValues[] = $request->$field;
                }
            }

            if (empty($updateFields)) {
                return response()->json(['message' => 'No fields to update'], 400);
            }

            $updateValues[] = $id;

            // Update the oil_purchase table
            DB::update(
                'UPDATE oil_purchase SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?',
                $updateValues
            );

            DB::commit();

            return response()->json(['message' => 'Oil purchase updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update oil purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete an oil purchase
    public function destroy($id)
    {
        // Check if the oil purchase exists
        $oilPurchase = DB::select('SELECT * FROM oil_purchase WHERE id = ?', [$id]);
        if (empty($oilPurchase)) {
            return response()->json(['message' => 'Oil purchase not found'], 404);
        }

        DB::beginTransaction();

        try {
            // Get purchase details before deleting
            $purchase = $oilPurchase[0];

            // If stock was updated, revert the tank level
            if ($purchase->stock_update == 1 && $purchase->recieved_qty && $purchase->tank_id) {
                DB::update(
                    'UPDATE tanks SET current_level = GREATEST(current_level - ?, 0) WHERE id = ?',
                    [$purchase->recieved_qty, $purchase->tank_id]
                );
            }

            // Delete the purchase
            DB::delete('DELETE FROM oil_purchase WHERE id = ?', [$id]);

            DB::commit();

            return response()->json(['message' => 'Oil purchase deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete oil purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅Receive order and distribute to tanks - UPDATED WITH SHORTAGE LOGIC
    public function receiveOrder(Request $request, $id)
    {
        $validated = $request->validate([
            'receive_date' => 'required|date',
                    'shift_id' => 'required|integer|exists:shifts,id',  // ✅ ADD SHIFT VALIDATION

            'this_receive_qty' => 'required|numeric|min:0.01',
            'this_shortage_qty' => 'required|numeric|min:0',
            'net_received_qty' => 'required|numeric|min:0.01',
            'product_id' => 'required|integer',
            'station_id' => 'required|integer',
            'invoice_number' => 'nullable|string|max:45',
            'reference_number' => 'nullable|string|max:45',
            'vehicle_number' => 'required|string|max:45',
            'shortage' => 'required|numeric|min:0',
            'tanks' => 'required|array|min:1',
            'tanks.*.tank_id' => 'required|integer',
            'tanks.*.quantity' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Get current purchase details
            $purchase = DB::select('SELECT * FROM oil_purchase WHERE id = ?', [$id]);
            if (empty($purchase)) {
                return response()->json(['message' => 'Purchase not found'], 404);
            }

            $purchase = $purchase[0];
            $orderedQty = floatval($purchase->qty);
            $alreadyReceived = floatval($purchase->recieved_qty) ?? 0;

            // Get values from request
            $thisReceiveQty = floatval($validated['this_receive_qty']);
            $thisShortageQty = floatval($validated['this_shortage_qty']);
            $netReceivedQty = floatval($validated['net_received_qty']);
            $shiftId = $validated['shift_id'];  // ✅ GET SHIFT ID


            // Validate this receive calculation
            if (abs($thisReceiveQty - $thisShortageQty - $netReceivedQty) > 0.01) {
                return response()->json([
                    'message' => 'Quantity calculation error',
                    'formula' => 'This Receive - This Shortage = Net Received',
                    'calculation' => "$thisReceiveQty - $thisShortageQty = " . ($thisReceiveQty - $thisShortageQty),
                    'net_received' => $netReceivedQty
                ], 400);
            }

            // Check if this receive exceeds remaining ordered quantity
            $remainingOrdered = $orderedQty - $alreadyReceived;
            if ($thisReceiveQty > $remainingOrdered) {
                return response()->json([
                    'message' => 'This receive exceeds remaining ordered quantity',
                    'ordered_qty' => $orderedQty,
                    'already_received' => $alreadyReceived,
                    'remaining' => $remainingOrdered,
                    'trying_to_receive' => $thisReceiveQty,
                    'exceeds_by' => $thisReceiveQty - $remainingOrdered
                ], 400);
            }

            // Calculate total distributed from tanks in THIS receive
            $totalDistributed = 0;
            $tankIds = [];

            foreach ($validated['tanks'] as $tank) {
                $quantity = floatval($tank['quantity']);
                $totalDistributed += $quantity;
                $tankIds[] = $tank['tank_id'];
            }

            // VALIDATION: Check if distributed quantity matches net received
            if (abs($netReceivedQty - $totalDistributed) > 0.01) {
                return response()->json([
                    'message' => 'Distributed quantity does not match net received quantity',
                    'net_received' => $netReceivedQty,
                    'distributed_qty' => $totalDistributed,
                    'difference' => $netReceivedQty - $totalDistributed
                ], 400);
            }

            // Calculate new total received
            $newTotalReceived = $alreadyReceived + $thisReceiveQty;
            $newNetAddedToTanks = $alreadyReceived + $netReceivedQty;

            // STRICT VALIDATION
            if ($newTotalReceived > $orderedQty && ($newTotalReceived - $orderedQty) > 0.01) {
                return response()->json([
                    'message' => 'Cannot receive more than ordered quantity',
                    'ordered_qty' => $orderedQty,
                    'already_received' => $alreadyReceived,
                    'trying_to_receive' => $thisReceiveQty,
                    'new_total_would_be' => $newTotalReceived,
                    'max_allowed' => $orderedQty - $alreadyReceived,
                    'exceeded_by' => $newTotalReceived - $orderedQty
                ], 400);
            }

            $tankIdsStr = implode(',', $tankIds);
            $isFullyReceived = abs($orderedQty - $newTotalReceived) <= 0.01;

            // ✅ HANDLE INVOICE IMAGE UPLOAD - CORRECTED CODE
            $invoiceImagePath = null;

            // Check if image is sent as base64 in JSON
            if ($request->has('invoice_image') && !empty($request->invoice_image)) {

                try {
                    $imageData = $request->invoice_image;

                    // CASE 1: Base64 string se image handle karna
                    if (is_string($imageData) && strpos($imageData, 'base64,') !== false) {

                        // Extract image type from base64 header
                        preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches);
                        $imageType = isset($matches[1]) ? $matches[1] : 'jpg';

                        // Allowed image types
                        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (!in_array(strtolower($imageType), $allowedTypes)) {
                            throw new Exception("Invalid image type. Allowed: " . implode(', ', $allowedTypes));
                        }

                        // Remove base64 header
                        $imageData = explode('base64,', $imageData)[1];

                        // Fix spaces and decode
                        $imageData = str_replace(' ', '+', $imageData);
                        $decoded = base64_decode($imageData, true);

                        if ($decoded === false) {
                            throw new Exception("Invalid base64 image data");
                        }

                        // Generate filename and path
                        $filename = time() . '_' . uniqid() . '.' . $imageType;
                        $destinationPath = public_path('assets/uploads/invoices');

                        // Create directory if not exists
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }

                        // Save file
                        $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
                        file_put_contents($fullPath, $decoded);

                        // Store relative path for database
                        $invoiceImagePath = 'assets/uploads/invoices/' . $filename;
                    }

                    // CASE 2: Agar already file object hai (uploaded file)
                    else if ($imageData instanceof \Illuminate\Http\UploadedFile) {
                        $file = $imageData;
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $destinationPath = public_path('assets/uploads/invoices');

                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }

                        $file->move($destinationPath, $filename);
                        $invoiceImagePath = 'assets/uploads/invoices/' . $filename;
                    } else {
                        throw new Exception("Invalid image format");
                    }

                } catch (Exception $e) {
                    throw new Exception("Image upload failed: " . $e->getMessage());
                }
            }
            // Check if file is uploaded via multipart form
            else if ($request->hasFile('invoice_image')) {
                $file = $request->file('invoice_image');

                // Validate file
                if (!$file->isValid()) {
                    throw new Exception("Invalid file upload");
                }

                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('assets/uploads/invoices');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);
                $invoiceImagePath = 'assets/uploads/invoices/' . $filename;
            }

            // UPDATE oil_purchase
            DB::update(
                'UPDATE oil_purchase SET 
        recieving_date = ?,
        recieved_qty = ?,
        tank_id = CONCAT(COALESCE(tank_id, ""), ?, ?),
        recive_status = ?,
        stock_update = 1,
        updated_at = NOW()
        WHERE id = ?',
                [
                    $validated['receive_date'],
                    $newTotalReceived,
                    ($purchase->tank_id ? ',' : ''),
                    $tankIdsStr,
                    $isFullyReceived ? 'Recived' : 'Not-Recived',
                    $id
                ]
            );

            // Get additional fields
            $invoiceNumber = $validated['invoice_number'] ?? null;
            $referenceNumber = $validated['reference_number'] ?? null;
            $vehicleNumber = $validated['vehicle_number'] ?? null;
            $shortage = $validated['shortage'] ?? 0;

            // Update tank levels and record in history
            $lastReceiveId = null;

            foreach ($validated['tanks'] as $tank) {
                $quantity = floatval($tank['quantity']);
                DB::update(
                    'UPDATE tanks SET current_level = current_level + ? WHERE id = ?',
                    [$quantity, $tank['tank_id']]
                );

                // ✅ INSERT INTO oil_recived_tanks with ALL fields including invoice_image
                DB::insert(
                    'INSERT INTO oil_recived_tanks (
                oil_purchase_id, 
                tanks_id, 
                recived_qty, 
                recive_date,
                                shift_id,  
                inovice_number,
                reference_number,
                vehicle_number,
                invoice_image,
                shortage,
                created_at
            ) VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                    [
                        $id,
                        $tank['tank_id'],
                        $quantity,
                        $validated['receive_date'],
                                            $shiftId,  

                        $invoiceNumber,
                        $referenceNumber,
                        $vehicleNumber,
                        $invoiceImagePath,  
                        $shortage
                    ]
                );

                $lastReceiveId = DB::getPdo()->lastInsertId();

                // Insert into fuel_inventory_layers
                DB::insert(
                    'INSERT INTO fuel_inventory_layers
            (tank_id, product_id, remaining_qty, rate)
            VALUES (?, ?, ?, ?)',
                    [
                        $tank['tank_id'],
                        $purchase->product_id,
                        $quantity,
                        $purchase->rate
                    ]
                );
            }

            DB::commit();

            // Return full URL for image
            $fullImageUrl = $invoiceImagePath ? url($invoiceImagePath) : null;

            return response()->json([
                'message' => 'Order received successfully',
                'receive_id' => $lastReceiveId,
                'id' => $id,  // oil_purchase_id
                            'shift_id' => $shiftId,  // ✅ RETURN SHIFT ID

                'ordered_quantity' => $orderedQty,
                'already_received_before' => $alreadyReceived,
                'this_receive' => $thisReceiveQty,
                'shortage_this_time' => $thisShortageQty,
                'net_added_to_tanks' => $netReceivedQty,
                'total_received_so_far' => $newTotalReceived,
                'total_net_in_tanks' => $newNetAddedToTanks,
                'remaining' => max(0, $orderedQty - $newTotalReceived),
                'status' => $isFullyReceived ? 'Fully Received' : 'Partially Received',
                'calculation' => "This: $thisReceiveQty - Shortage: $thisShortageQty = Net: $netReceivedQty",
                'invoice_image' => $invoiceImagePath,
                'invoice_image_url' => $fullImageUrl
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to receive order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to receive order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅Receive order and distribute to tanks - UPDATED WITH SHORTAGE LOGIC
    public function receiveOrder2(Request $request, $id)
    {
        $validated = $request->validate([
            'receive_date' => 'required|date',
            'this_receive_qty' => 'required|numeric|min:0.01',
            'this_shortage_qty' => 'required|numeric|min:0',
            'net_received_qty' => 'required|numeric|min:0.01',
            'product_id' => 'required|integer',
            'station_id' => 'required|integer',
            'invoice_number' => 'nullable|string|max:45',
            'reference_number' => 'nullable|string|max:45',
            'vehicle_number' => 'required|string|max:45',
            'shortage' => 'required|numeric|min:0',
            'tanks' => 'required|array|min:1',
            'tanks.*.tank_id' => 'required|integer',
            'tanks.*.quantity' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Get current purchase details
            $purchase = DB::select('SELECT * FROM oil_purchase WHERE id = ?', [$id]);
            if (empty($purchase)) {
                return response()->json(['message' => 'Purchase not found'], 404);
            }

            $purchase = $purchase[0];
            $orderedQty = floatval($purchase->qty);
            $alreadyReceived = floatval($purchase->recieved_qty) ?? 0;

            // Get values from request
            $thisReceiveQty = floatval($validated['this_receive_qty']);
            $thisShortageQty = floatval($validated['this_shortage_qty']);
            $netReceivedQty = floatval($validated['net_received_qty']);

            // Validate this receive calculation
            if (abs($thisReceiveQty - $thisShortageQty - $netReceivedQty) > 0.01) {
                return response()->json([
                    'message' => 'Quantity calculation error',
                    'formula' => 'This Receive - This Shortage = Net Received',
                    'calculation' => "$thisReceiveQty - $thisShortageQty = " . ($thisReceiveQty - $thisShortageQty),
                    'net_received' => $netReceivedQty
                ], 400);
            }

            // Check if this receive exceeds remaining ordered quantity
            $remainingOrdered = $orderedQty - $alreadyReceived;
            if ($thisReceiveQty > $remainingOrdered) {
                return response()->json([
                    'message' => 'This receive exceeds remaining ordered quantity',
                    'ordered_qty' => $orderedQty,
                    'already_received' => $alreadyReceived,
                    'remaining' => $remainingOrdered,
                    'trying_to_receive' => $thisReceiveQty,
                    'exceeds_by' => $thisReceiveQty - $remainingOrdered
                ], 400);
            }

            // Calculate total distributed from tanks in THIS receive
            $totalDistributed = 0;
            $tankIds = [];

            foreach ($validated['tanks'] as $tank) {
                $quantity = floatval($tank['quantity']);
                $totalDistributed += $quantity;
                $tankIds[] = $tank['tank_id'];
            }

            // VALIDATION: Check if distributed quantity matches net received
            if (abs($netReceivedQty - $totalDistributed) > 0.01) {
                return response()->json([
                    'message' => 'Distributed quantity does not match net received quantity',
                    'net_received' => $netReceivedQty,
                    'distributed_qty' => $totalDistributed,
                    'difference' => $netReceivedQty - $totalDistributed
                ], 400);
            }

            // Calculate new total received
            $newTotalReceived = $alreadyReceived + $thisReceiveQty;
            $newNetAddedToTanks = $alreadyReceived + $netReceivedQty;

            // STRICT VALIDATION
            if ($newTotalReceived > $orderedQty && ($newTotalReceived - $orderedQty) > 0.01) {
                return response()->json([
                    'message' => 'Cannot receive more than ordered quantity',
                    'ordered_qty' => $orderedQty,
                    'already_received' => $alreadyReceived,
                    'trying_to_receive' => $thisReceiveQty,
                    'new_total_would_be' => $newTotalReceived,
                    'max_allowed' => $orderedQty - $alreadyReceived,
                    'exceeded_by' => $newTotalReceived - $orderedQty
                ], 400);
            }

            $tankIdsStr = implode(',', $tankIds);
            $isFullyReceived = abs($orderedQty - $newTotalReceived) <= 0.01;

            // ✅ HANDLE INVOICE IMAGE UPLOAD FROM JSON REQUEST
            $invoiceImagePath = null;

            // Check if image is sent as base64 in JSON
            if ($request->has('invoice_image') && !empty($request->invoice_image)) {
                //dd("SAMAD",$request->invoice_image);
                $imageData = $request->invoice_image;
                //dd($imageData->getClientOriginalExtension());
                //preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches);

                $imageType = $imageData->getClientOriginalExtension();


                if (strpos($imageData, 'base64,') !== false) {
                    $imageData = explode('base64,', $imageData)[1];
                }

                $imageData = str_replace(' ', '+', $imageData); // important fix for form submissions
                $decoded = base64_decode($imageData, true);




                //dd($imageType);
                //$imageData = substr($imageData, strpos($imageData, ',') + 1);

                // Decode
                //$imageData = base64_decode($imageData);

                //dd($imageType);
                // Remove base64 header if present
                //$imageData = substr($imageData, strpos($imageData, ',') + 1);
                //$imageType = strtolower($type[1]); // jpg, png, jpeg
                //$imageType = $imageData->getMimeType();
                // Validate image type
                //if (!in_array($imageType, ['jpg', 'jpeg', 'png'])) {
                //$imageType = 'jpg';
                //}

                $imageData = base64_decode($imageData);
                $filename = time() . '_' . uniqid() . '.' . $imageType;

                $destinationPath = public_path('assets\uploads\invoices');
                $destinationPath = $destinationPath . "\\" . $filename;
                //file_put_contents($destinationPath,$imageData );
                $imageData->move($destinationPath, );
                //dd($destinationPath);
                // Create directory if not exists

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                //file_put_contents($destinationPath . $filename, $imageData);
                $invoiceImagePath = 'assets\uploads\invoices' . $filename;

            }
            // OR check if file is uploaded via multipart form
            else if ($request->hasFile('invoice_image')) {
                //dd("Samad");
                $file = $request->file('invoice_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('assets/uploads/invoices/');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);
                $invoiceImagePath = 'assets/uploads/invoices/' . $filename;
            }

            // UPDATE oil_purchase
            DB::update(
                'UPDATE oil_purchase SET 
            recieving_date = ?,
            recieved_qty = ?,
            tank_id = CONCAT(COALESCE(tank_id, ""), ?, ?),
            recive_status = ?,
            stock_update = 1,
            updated_at = NOW()
            WHERE id = ?',
                [
                    $validated['receive_date'],
                    $newTotalReceived,
                    ($purchase->tank_id ? ',' : ''),
                    $tankIdsStr,
                    $isFullyReceived ? 'Recived' : 'Not-Recived',
                    $id
                ]
            );

            // Get additional fields
            $invoiceNumber = $validated['invoice_number'] ?? null;
            $referenceNumber = $validated['reference_number'] ?? null;
            $vehicleNumber = $validated['vehicle_number'] ?? null;
            $shortage = $validated['shortage'] ?? 0;

            // Update tank levels and record in history
            $lastReceiveId = null;

            foreach ($validated['tanks'] as $tank) {
                $quantity = floatval($tank['quantity']);
                DB::update(
                    'UPDATE tanks SET current_level = current_level + ? WHERE id = ?',
                    [$quantity, $tank['tank_id']]
                );

                // ✅ INSERT INTO oil_recived_tanks with ALL fields including invoice_image
                DB::insert(
                    'INSERT INTO oil_recived_tanks (
                    oil_purchase_id, 
                    tanks_id, 
                    recived_qty, 
                    recive_date,
                    inovice_number,
                    reference_number,
                    vehicle_number,
                    invoice_image,
                    shortage,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                    [
                        $id,
                        $tank['tank_id'],
                        $quantity,
                        $validated['receive_date'],
                        $invoiceNumber,
                        $referenceNumber,
                        $vehicleNumber,
                        $invoiceImagePath,  // ✅ IMAGE PATH INSERTED HERE
                        $shortage
                    ]
                );

                $lastReceiveId = DB::getPdo()->lastInsertId();

                // Insert into fuel_inventory_layers
                DB::insert(
                    'INSERT INTO fuel_inventory_layers
                (tank_id, product_id, remaining_qty, rate)
                VALUES (?, ?, ?, ?)',
                    [
                        $tank['tank_id'],
                        $purchase->product_id,
                        $quantity,
                        $purchase->rate
                    ]
                );
            }

            DB::commit();

            // Return full URL for image
            $fullImageUrl = $invoiceImagePath ? url($invoiceImagePath) : null;

            return response()->json([
                'message' => 'Order received successfully',
                'receive_id' => $lastReceiveId,
                'id' => $id,  // oil_purchase_id
                'ordered_quantity' => $orderedQty,
                'already_received_before' => $alreadyReceived,
                'this_receive' => $thisReceiveQty,
                'shortage_this_time' => $thisShortageQty,
                'net_added_to_tanks' => $netReceivedQty,
                'total_received_so_far' => $newTotalReceived,
                'total_net_in_tanks' => $newNetAddedToTanks,
                'remaining' => max(0, $orderedQty - $newTotalReceived),
                'status' => $isFullyReceived ? 'Fully Received' : 'Partially Received',
                'calculation' => "This: $thisReceiveQty - Shortage: $thisShortageQty = Net: $netReceivedQty",
                'invoice_image' => $invoiceImagePath,
                'invoice_image_url' => $fullImageUrl
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to receive order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to receive order',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // ✅ 2. NEW: Get oil purchases by shift_id
public function getByShift($shiftId)
{
    $oilPurchases = DB::select(
        'SELECT 
            op.id, 
            op.order_date, 
            op.recieving_date, 
            op.payment_status, 
            op.recieved_qty, 
            op.recive_status, 
            op.rate, 
            op.qty, 
            op.invoice_no, 
            op.ref_num, 
            op.stock_update, 
            op.created_at, 
            op.updated_at,
            op.supplier_id, 
            op.station_id, 
            op.shift_id, 
            op.product_id,
            a.name AS supplier_name,
            s.name AS station_name,
            sh.shift_no,
            p.name AS product_name,
            COALESCE(
                (SELECT SUM(ap.ammount) 
                 FROM ammount_paid ap 
                 WHERE ap.oil_purchase_id = op.id 
                 AND ap.method = "cash"
                 AND ap.shift_id = ?),
                0
            ) as total_cash_paid,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM ammount_paid ap 
                    WHERE ap.oil_purchase_id = op.id 
                    AND ap.method = "cash"
                    AND ap.shift_id = ?
                ) THEN "cash"
                ELSE "no_cash"
            END as has_cash_payment,
            COALESCE(
                (SELECT COUNT(*) 
                 FROM ammount_paid ap 
                 WHERE ap.oil_purchase_id = op.id 
                 AND ap.method = "cash"
                 AND ap.shift_id = ?),
                0
            ) as cash_payment_count
         FROM oil_purchase op
         LEFT JOIN accounts a ON op.supplier_id = a.id OR op.supplier_id = a.stationrow_id
         LEFT JOIN stations s ON op.station_id = s.id 
         LEFT JOIN shifts sh ON op.shift_id = sh.id OR op.shift_id = sh.stationrow_id
         LEFT JOIN products p ON op.product_id = p.id
         WHERE op.shift_id = ?
         AND EXISTS (
            SELECT 1 FROM ammount_paid ap 
            WHERE ap.oil_purchase_id = op.id 
            AND ap.method = "cash"
            AND ap.shift_id = ?
         )
         ORDER BY op.created_at DESC',
        [$shiftId, $shiftId, $shiftId, $shiftId, $shiftId]
    );

    return response()->json($oilPurchases);
}

    // ✅ Get shortage payments by shift_id
    public function getShortagePaymentsByShift($shiftId)
    {
        $shortagePayments = DB::select(
            'SELECT 
            sapb.id,
            sapb.shift_id,
            sapb.oil_purchase_id,
            sapb.oil_recived_id,
            sapb.account_id,
            sapb.total_shortage,
            sapb.remaining_shortage,
            sapb.total_amount,
            sapb.remaining_amount,
            sapb.is_paid,
            sapb.created_at,
            sapb.payment_type,
            
            -- Purchase related info
            op.order_date,
            op.recieving_date,
            op.payment_status,
            op.rate,
            op.qty,
            op.invoice_no,
            op.ref_num,
            
            -- Supplier info
            a.name AS supplier_name,
            
            -- Station info
            s.name AS station_name,
            s.id AS station_id,
            
            -- Shift info
            sh.shift_no,
            
            -- Product info
            p.name AS product_name,
            
            -- Tank receive info
            ort.recive_date AS oil_receive_date,
            ort.recived_qty,
            ort.vehicle_number,
            ort.inovice_number AS receive_invoice_no,
            ort.reference_number AS receive_ref_no,
            
            -- Account/Bank info
            acc.name AS bank_name,
            acc.account_number,
            acc.bank_name AS bank_details,
            
            -- Calculate if this payment was cash or bank
            CASE 
                WHEN sapb.account_id IS NOT NULL THEN "bank"
                ELSE "cash"
            END as payment_method,
            
            -- Get station row id for reference
            op.stationrow_id,
            ort.stationrow_id as tank_stationrow_id
            
        FROM shortage_ammount_paid_back sapb
        
        -- Join with oil_purchase
        LEFT JOIN oil_purchase op ON sapb.oil_purchase_id = op.id
        
        -- Join with supplier (accounts)
        LEFT JOIN accounts a ON op.supplier_id = a.id OR op.supplier_id = a.stationrow_id
        
        -- Join with station
        LEFT JOIN stations s ON op.station_id = s.id
        
        -- Join with shift
        LEFT JOIN shifts sh ON sapb.shift_id = sh.id OR sapb.shift_id = sh.stationrow_id
        
        -- Join with product
        LEFT JOIN products p ON op.product_id = p.id
        
        -- Join with oil received tanks
        LEFT JOIN oil_recived_tanks ort ON sapb.oil_recived_id = ort.id
        
        -- Join with accounts for bank details
        LEFT JOIN accounts acc ON sapb.account_id = acc.id OR sapb.account_id = acc.stationrow_id
        
        WHERE sapb.shift_id = ?
        
        ORDER BY sapb.created_at DESC',
            [$shiftId]
        );

        return response()->json([
            'success' => true,
            'shift_id' => $shiftId,
            'count' => count($shortagePayments),
            'payments' => $shortagePayments
        ]);
    }

    public function updateInvoice(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'invoice_no' => 'required|string|max:45',
            'ref_num' => 'nullable|string|max:45'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        DB::update(
            'UPDATE oil_purchase SET 
         invoice_no = ?,
         ref_num = ?,
         updated_at = NOW()
         WHERE id = ?',
            [
                $request->invoice_no,
                $request->ref_num,
                $id
            ]
        );

        return response()->json(['message' => 'Invoice details updated successfully']);
    }

    // ✅ NEW: Get receive history for a purchase
    public function getReceiveHistory($purchaseId)
    {
        try {
            $history = DB::select(
                'SELECT ort.*, t.name as tank_name, 
                t.capacity, p.name as product_name,
                ort.inovice_number,     
                ort.reference_number,   
                ort.shortage            
         FROM oil_recived_tanks ort
         LEFT JOIN tanks t ON ort.tanks_id = t.id OR ort.tanks_id = t.stationrow_id
         LEFT JOIN products p ON t.product_id = p.id
         WHERE ort.oil_purchase_id = ?
         ORDER BY ort.recive_date DESC, ort.created_at DESC',
                [$purchaseId]
            );

            return response()->json($history);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch receive history',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // ✅ NEW: Check if purchase can still receive more
    public function canReceiveMore($purchaseId)
    {
        try {
            $purchase = DB::select('SELECT qty, recieved_qty FROM oil_purchase WHERE id = ?', [$purchaseId]);

            if (empty($purchase)) {
                return response()->json(['can_receive' => false, 'message' => 'Purchase not found']);
            }

            $purchase = $purchase[0];
            $orderedQty = $purchase->qty;
            $alreadyReceived = $purchase->recieved_qty || 0;
            $remaining = $orderedQty - $alreadyReceived;

            return response()->json([
                'can_receive' => $remaining > 0,
                'ordered_qty' => $orderedQty,
                'already_received' => $alreadyReceived,
                'remaining' => $remaining
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'can_receive' => false,
                'message' => 'Error checking receive status'
            ]);
        }
    }

    // ✅ Get shortage details for a purchase
    public function getShortageDetails($purchaseId)
    {
        try {
            $purchase = DB::select('SELECT id, rate, qty, station_id FROM oil_purchase WHERE id = ?', [$purchaseId]);
            if (empty($purchase)) {
                return response()->json(['error' => 'Purchase not found'], 404);
            }

            $purchase = $purchase[0];
            $rate = floatval($purchase->rate);

            $receives = DB::select(
                'SELECT ort.id as receive_id, ort.shortage, ort.recived_qty, ort.recive_date
             FROM oil_recived_tanks ort
             WHERE ort.oil_purchase_id = ? AND ort.shortage > 0
             ORDER BY ort.recive_date ASC',
                [$purchaseId]
            );

            $paidShortages = DB::select(
                'SELECT DISTINCT oil_recived_id FROM shortage_ammount_paid_back 
             WHERE oil_purchase_id = ? AND is_paid = 1',
                [$purchaseId]
            );

            $paidReceiveIds = array_column($paidShortages, 'oil_recived_id');

            $totalPendingShortage = 0;
            $pendingReceives = [];

            foreach ($receives as $receive) {
                if (!in_array($receive->receive_id, $paidReceiveIds)) {
                    $totalPendingShortage += floatval($receive->shortage);
                    $pendingReceives[] = [
                        'receive_id' => $receive->receive_id,
                        'shortage' => floatval($receive->shortage),
                        'recived_qty' => floatval($receive->recived_qty),
                        'recive_date' => $receive->recive_date
                    ];
                }
            }

            $totalPendingAmount = $totalPendingShortage * $rate;
            $isFullyPaid = ($totalPendingShortage <= 0);

            return response()->json([
                'success' => true,
                'purchase_id' => $purchaseId,
                'rate' => $rate,
                'total_shortage' => $totalPendingShortage,
                'total_amount' => $totalPendingAmount,
                'is_already_paid' => $isFullyPaid,
                'station_id' => $purchase->station_id,
                'pending_receives' => $pendingReceives,
                'paid_receive_ids' => $paidReceiveIds
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Process shortage payment
    public function processShortagePayment(Request $request, $purchaseId)
    {
        $validator = Validator::make($request->all(), [
            'shift_id' => 'required|integer|exists:shifts,id',
            'payment_method' => 'required|in:cash,bank',
            'account_id' => 'required_if:payment_method,bank|nullable|integer|exists:accounts,id',
            'total_shortage' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'receive_ids' => 'required|array|min:1',
            'receive_ids.*' => 'integer|exists:oil_recived_tanks,id',
            'is_partial' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $purchase = DB::select('SELECT station_id, rate FROM oil_purchase WHERE id = ?', [$purchaseId]);
            if (empty($purchase)) {
                return response()->json(['message' => 'Purchase not found'], 404);
            }

            $purchase = $purchase[0];
            $stationId = $purchase->station_id;
            $rate = floatval($purchase->rate);
            $totalAmount = floatval($request->total_amount);
            $totalShortage = floatval($request->total_shortage);
            $receiveIds = $request->receive_ids;
            $isPartial = $request->is_partial ?? false;
            $accountId = ($request->payment_method === 'bank') ? $request->account_id : null;

            // Check if any receive already paid
            $existingPaid = DB::select(
                'SELECT oil_recived_id FROM shortage_ammount_paid_back 
            WHERE oil_purchase_id = ? AND oil_recived_id IN (' . implode(',', $receiveIds) . ') AND is_paid = 1',
                [$purchaseId]
            );

            if (!empty($existingPaid)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Some receives are already paid',
                    'already_paid_ids' => array_column($existingPaid, 'oil_recived_id')
                ], 400);
            }

            // Get shortage amounts for selected receives
            $receivesData = DB::select(
                'SELECT id, shortage FROM oil_recived_tanks 
            WHERE id IN (' . implode(',', $receiveIds) . ')'
            );

            // Calculate total shortage from selected receives
            $calculatedShortage = 0;
            foreach ($receivesData as $rec) {
                $calculatedShortage += floatval($rec->shortage);
            }

            // Validate shortage amount matches
            if (abs($calculatedShortage - $totalShortage) > 0.01) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Shortage amount mismatch',
                    'calculated' => $calculatedShortage,
                    'provided' => $totalShortage
                ], 400);
            }

            // ✅ BANK PAYMENT - Update site_total_ammount
            if ($request->payment_method === 'bank' && $accountId) {
                $currentBalance = DB::selectOne(
                    'SELECT amount FROM site_total_ammount 
                WHERE station_id = ? AND account_id = ?
                ORDER BY created_at DESC LIMIT 1',
                    [$stationId, $accountId]
                );

                $previousAmount = $currentBalance ? floatval($currentBalance->amount) : 0;
                $newAmount = $previousAmount + $totalAmount; // Add payment to bank balance

                DB::insert(
                    'INSERT INTO site_total_ammount 
                (station_id, account_id, amount, previous_amount, date, created_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())',
                    [$stationId, $accountId, $newAmount, $previousAmount]
                );
            }

            // ✅ Insert payment records for each selected receive
            foreach ($receiveIds as $receiveId) {
                // Find the shortage for this receive
                $receiveShortageData = null;
                foreach ($receivesData as $rec) {
                    if ($rec->id == $receiveId) {
                        $receiveShortageData = $rec;
                        break;
                    }
                }

                if (!$receiveShortageData) {
                    continue;
                }

                $receiveShortageAmount = floatval($receiveShortageData->shortage);

                // ✅ CORRECT CALCULATION: shortage * rate
                $correctAmountForThisReceive = $receiveShortageAmount * $rate;

                // For partial payments, calculate proportionally
                $paymentForThisReceive = $correctAmountForThisReceive;
                if ($isPartial) {
                    // Calculate what portion of total payment goes to this receive
                    $proportion = $receiveShortageAmount / $calculatedShortage;
                    $paymentForThisReceive = $totalAmount * $proportion;
                }

                // ✅ Insert record with correct amounts
                DB::insert(
                    'INSERT INTO shortage_ammount_paid_back 
                (oil_purchase_id, oil_recived_id, shift_id, account_id, 
                 total_shortage, total_amount, is_paid, payment_type, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW())',
                    [
                        $purchaseId,
                        $receiveId,
                        $request->shift_id,
                        $accountId,
                        $receiveShortageAmount,  // ✅ Correct shortage quantity
                        $paymentForThisReceive,   // ✅ Correct amount (shortage * rate)
                        $isPartial ? 'partial' : 'full'
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shortage payment recorded successfully',
                'payment_method' => $request->payment_method,
                'total_shortage' => $totalShortage,
                'total_amount' => $totalAmount,
                'rate' => $rate,
                'paid_receive_ids' => $receiveIds,
                'bank_balance_updated' => ($request->payment_method === 'bank') ? 'yes' : 'no'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process shortage payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process shortage payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getShortagePaymentHistory($purchaseId)
    {
        try {
            $payments = DB::select(
                'SELECT sapb.*, 
                ort.recived_qty, ort.recive_date,
                s.shift_no,
                a.name as bank_name,
                a.account_number
            FROM shortage_ammount_paid_back sapb
            LEFT JOIN oil_recived_tanks ort ON sapb.oil_recived_id = ort.id
            LEFT JOIN shifts s ON sapb.shift_id = s.id OR sapb.shift_id = s.stationrow_id
            LEFT JOIN accounts a ON sapb.account_id = a.id OR sapb.account_id = a.stationrow_id
            WHERE sapb.oil_purchase_id = ?
            ORDER BY sapb.created_at DESC',
                [$purchaseId]
            );

            // ✅ Ensure amounts are proper
            foreach ($payments as $payment) {
                // If total_amount is obviously wrong (like > shortage * 1000), recalculate
                $shortage = floatval($payment->total_shortage);
                $totalAmount = floatval($payment->total_amount);

                // Get rate from oil_purchase
                $purchase = DB::selectOne('SELECT rate FROM oil_purchase WHERE id = ?', [$purchaseId]);
                if ($purchase) {
                    $expectedAmount = $shortage * floatval($purchase->rate);
                    // If amount is wildly different (off by factor of 100 or more)
                    if (abs($totalAmount - $expectedAmount) > $expectedAmount && $expectedAmount > 0) {
                        // Fix it in response (optional)
                        $payment->total_amount = $expectedAmount;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'payments' => $payments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Check if shortage payment already exists
    public function checkShortagePaymentStatus($purchaseId)
    {
        try {
            $payment = DB::selectOne(
                'SELECT * FROM shortage_ammount_paid_back 
             WHERE oil_purchase_id = ? AND is_paid = 1',
                [$purchaseId]
            );

            return response()->json([
                'is_paid' => !empty($payment),
                'payment_details' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'is_paid' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

}