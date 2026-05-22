<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LubeController extends Controller
{
    // ✅ 1. Get all documents with joined details
    public function index()
    {
        $docs = DB::table('lube_documents as d')
            ->leftJoin('accounts as a', function ($join) {
                $join->on('d.account_id', '=', 'a.id')
                    ->orOn('d.account_id', '=', 'a.stationrow_id');
            })
            ->leftJoin('stations as s', 'd.station_id', '=', 's.id')
            ->select(
                'd.id',
                'd.shift_id',
                'd.doc_type',
                'd.invoice_no',
                'd.date',
                'd.payment_status',
                'd.payment_method',
                'a.type as account_type',
                'a.name as account_name',
                's.name as station_name',
                DB::raw('(SELECT SUM(line_amount + tax_amount) FROM lube_lines WHERE document_id = d.id) as total_amount')
            )
            ->orderByDesc('d.id')
            ->get();

        return response()->json($docs);
    }

    // ✅ 2. Get document by ID with its line items
    public function show($id)
    {
        $document = DB::table('lube_documents as d')
            ->leftJoin('accounts as a', function ($join) {
                $join->on('d.account_id', '=', 'a.id')
                    ->orOn('d.account_id', '=', 'a.stationrow_id');
            })
            ->leftJoin('stations as s', 'd.station_id', '=', 's.id')
            ->select(
                'd.*',
                'a.name as account_name',
                'a.type as account_type',
                's.name as station_name',
                's.id as station_id'
            )
            ->where('d.id', $id)
            ->first();

        if (!$document)
            return response()->json(['message' => 'Not found'], 404);

        $lines = DB::table('lube_lines as l')
            ->join('products as p', 'p.id', '=', 'l.product_id')
            ->select('l.*', 'p.name as product_name', 'p.category')
            ->where('l.document_id', $id)
            ->get();

        // Calculate totals
        $totalAmount = 0;
        foreach ($lines as $line) {
            $totalAmount += ($line->line_amount + $line->tax_amount);
        }

        // Get payment history
        $payments = DB::table('ammount_paid as ap')
            ->leftJoin('accounts as a', function ($join) {
                $join->on('ap.account_id', '=', 'a.id')
                    ->orOn('ap.account_id', '=', 'a.stationrow_id');
            })
            ->leftJoin('shifts as sh', function ($join) {
                $join->on('ap.shift_id', '=', 'sh.id')
                    ->orOn('ap.shift_id', '=', 'sh.stationrow_id');
            })

            ->select(
                'ap.*',
                'a.name as account_name',
                'a.bank_name',
                'a.account_number',
                'sh.shift_no'
            )
            ->where('ap.lube_id', $id)
            ->orderBy('ap.created_at', 'ASC')
            ->get();
        ;

        // Calculate total paid
        $totalPaid = $payments->where('type', 'debit')->sum('ammount') -
            $payments->where('type', 'credit')->sum('ammount');

        return response()->json([
            'document' => $document,
            'lines' => $lines,
            'total_amount' => $totalAmount,
            'payments' => $payments,
            'total_paid' => $totalPaid,
            'remaining_amount' => $totalAmount - $totalPaid
        ]);
    }

    // ✅ 3. Filter by station
    public function byStation($station_id)
    {
        $stationrec = DB::select("select * from stations where id =?", [$station_id]);
        if ($stationrec[0]->local == "1") {
            $docs = DB::table('lube_documents as d')
                ->join('stations as s', 's.id', '=', 'd.station_id')
                ->leftJoin('accounts as a', 'd.account_id', '=', 'a.stationrow_id')
                ->select('d.*', 's.name as station_name', 'a.name as account_name')
                ->where('d.station_id', $station_id)
                ->get();
        } else {
            $docs = DB::table('lube_documents as d')
                ->join('stations as s', 's.id', '=', 'd.station_id')
                ->join('accounts as a', 'a.id', '=', 'd.account_id')
                ->select('d.*', 's.name as station_name', 'a.name as account_name')
                ->where('d.station_id', $station_id)
                ->get();
        }

        return response()->json($docs);
    }

    // ✅ 4. Filter by account
    public function byAccount($account_id)
    {
        $docs = DB::table('lube_documents as d')
            ->join('accounts as a', function ($join) {
                $join->on('d.account_id', '=', 'a.id')
                    ->orOn('d.account_id', '=', 'a.stationrow_id');
            })
            ->join('stations as s', 'd.station_id', '=', 's.id')
            ->select(
                'd.*',
                'a.name as account_name',
                's.name as station_name'
            )
            ->where('d.account_id', $account_id)
            ->get();

        return response()->json($docs);
    }

    // ✅ 5. Filter by product (joins through lines)
    public function byProduct($product_id)
    {
        $docs = DB::table('lube_documents as d')
            ->join('lube_lines as l', function ($join) {
                $join->on('l.document_id', '=', 'd.id')
                    ->orOn('l.document_id', '=', 'd.stationrow_id');
            })
            ->join('products as p', 'l.product_id', '=', 'p.id')
            ->join('accounts as a', function ($join) {
                $join->on('d.account_id', '=', 'a.id')
                    ->orOn('d.account_id', '=', 'a.stationrow_id');
            })
            ->join('stations as s', 'd.station_id', '=', 's.id')
            ->select(
                'd.*',
                'p.name as product_name',
                'a.name as account_name',
                's.name as station_name'
            )
            ->where('p.id', $product_id)
            ->get();

        return response()->json($docs);
    }

    // ✅ 6. Create new document + lines - COMPLETE FIXED VERSION
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'station_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'doc_type' => 'required|in:purchase,sale',
            'account_id' => 'required|integer',
            'invoice_no' => 'nullable|string',
            'date' => 'required|date',
            'payment_status' => 'required|in:paid,not_paid,partial',
            'remarks' => 'nullable|string',
            'created_by' => 'required|integer',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|integer',
            'lines.*.qty' => 'required|numeric|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $doc_id = DB::table('lube_documents')->insertGetId([
                'station_id' => $req->station_id,
                'shift_id' => $req->shift_id,
                'doc_type' => $req->doc_type,
                'account_id' => $req->account_id,
                'invoice_no' => $req->invoice_no,
                'date' => $req->date,
                'payment_status' => $req->payment_status,
                'remarks' => $req->remarks,
                'created_by' => $req->created_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($req->lines as $line) {
                $qty = floatval($line['qty']);
                $unit_price = floatval($line['unit_price']);
                $tax_percent = floatval($line['tax_percent'] ?? 0);

                // ✅ REMOVED line_amount and tax_amount from insert
                // MySQL will calculate them automatically because they're GENERATED columns
                DB::table('lube_lines')->insert([
                    'document_id' => $doc_id,
                    'product_id' => $line['product_id'],
                    'qty' => $qty,
                    'unit_price' => $unit_price,
                    'tax_percent' => $tax_percent,
                    // 'line_amount' and 'tax_amount' are AUTO-CALCULATED by MySQL
                    'created_at' => now(),
                ]);
            }
            // ✅ ADD THIS: Update inventory based on doc_type
            if ($req->doc_type === 'purchase') {
                $this->updateInventoryAfterPurchase($doc_id);
            } elseif ($req->doc_type === 'sale') {
                $this->updateInventoryAfterSale($doc_id);
            }

            DB::commit();
            return response()->json(['message' => 'Created', 'id' => $doc_id], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create lube document: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create document: ' . $e->getMessage()], 500);
        }

    }

    // ✅ 7. Delete document
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Delete related payments first
            DB::table('ammount_paid')->where('lube_id', $id)->delete();

            // Delete lines
            DB::table('lube_lines')->where('document_id', $id)->delete();

            // Delete document
            DB::table('lube_documents')->where('id', $id)->delete();

            DB::commit();
            return response()->json(['message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete lube document: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete document'], 500);
        }
    }

    // ✅ 8. Mark as paid (simple)
    public function markAsPaid($id)
    {
        $updated = DB::table('lube_documents')
            ->where('id', $id)
            ->update(['payment_status' => 'paid', 'updated_at' => now()]);

        if ($updated) {
            return response()->json(['message' => 'Payment marked as paid successfully.'], 200);
        } else {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    }

    // ✅ 9. Get by owner
    public function getByOwner($id)
    {
        $docs = DB::table('lube_documents as d')
            ->join('stations as s', 'd.station_id', '=', 's.id')
            ->join('accounts as a', function ($join) {
                $join->on('d.account_id', '=', 'a.id')
                    ->orOn('d.account_id', '=', 'a.stationrow_id');
            })
            ->select(
                'd.*',
                's.name as station_name',
                'a.name as account_name'
            )
            ->where('s.user_id', $id)
            ->get();

        return response()->json($docs);
    }

    // ✅ 10. Get by employee
    public function getByEmployee($id)
    {
        try {
            $docs = DB::table('lube_documents as d')
                ->join('stations as s', 's.id', '=', 'd.station_id')
                ->join('accounts as a', function ($join) {
                    $join->on('d.account_id', '=', 'a.id')
                        ->orOn('d.account_id', '=', 'a.stationrow_id');
                })

                ->join('employees as e', 'e.station_id', '=', 's.id')
                ->select('d.*', 's.name as station_name', 'a.name as account_name')
                ->where('e.user_id', $id)
                ->get();

            return response()->json($docs);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employee documents: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch documents'], 500);
        }
    }

    // ✅ 11. Get by shift
    public function getByShift($shiftId)
    {
        $docs = DB::select("
        SELECT 
            d.id,
            d.shift_id,
            d.doc_type,
            d.invoice_no,
            d.date,
            d.payment_status,
            a.type as account_type,
            a.name as account_name,
            s.name as station_name,
            ap.method as payment_method,
            ap.ammount as payment_amount,  -- ✅ ACTUAL CASH PAID/RECEIVED
            ap.total_ammount as total_amount
        FROM lube_documents d
        LEFT JOIN accounts a ON a.stationrow_id = d.account_id OR a.id = d.account_id
        LEFT JOIN stations s ON s.id = d.station_id
        LEFT JOIN ammount_paid ap ON ap.lube_id = d.stationrow_id OR ap.lube_id = d.id AND ap.method = 'cash'
        WHERE d.shift_id = ?
        AND ap.id IS NOT NULL
        ORDER BY d.id DESC
    ", [$shiftId]);

        return response()->json($docs);
    }

    // ✅ 12. NEW: Update payment status with advanced payment management
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

        // Check if document exists (FIRST get document separately)
        $document = DB::table('lube_documents as d')
            ->join('accounts as a', function ($join) {
                $join->on('d.account_id', '=', 'a.id')
                    ->orOn('d.account_id', '=', 'a.stationrow_id');
            })

            ->leftJoin('stations as s', 's.id', '=', 'd.station_id')
            ->select('d.*', 'a.name as account_name', 's.name as station_name', 's.id as station_id')
            ->where('d.id', $id)
            ->first();

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // THEN calculate total amount separately
        $totalAmountResult = DB::table('lube_lines')
            ->where('document_id', $id)
            ->select(DB::raw('SUM(line_amount + tax_amount) as total_amount'))
            ->first();

        $totalAmount = $totalAmountResult ? floatval($totalAmountResult->total_amount) : 0;

        // ✅ BANK BALANCE CHECK (if bank payment)
        if ($request->payment_method === 'bank' && $request->account_id) {
            $currentBalance = DB::table('site_total_ammount')
                ->where('station_id', $document->station_id)
                ->where('account_id', $request->account_id)
                ->orderBy('created_at', 'DESC')
                ->first();

            $availableBalance = $currentBalance ? $currentBalance->amount : 0;

            if ($availableBalance < $totalAmount) {
                return response()->json([
                    'message' => 'Insufficient balance for full payment',
                    'available_balance' => $availableBalance,
                    'required_amount' => $totalAmount,
                    'short_by' => $totalAmount - $availableBalance
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Update the payment status
            DB::table('lube_documents')
                ->where('id', $id)
                ->update([
                    'payment_status' => $request->payment_status,
                    'updated_at' => now()
                ]);

            // ✅ BANK PAYMENT KE LIYE site_total_ammount UPDATE
            if ($request->payment_method === 'bank' && $request->account_id) {
                $currentSiteAmount = DB::table('site_total_ammount')
                    ->where('station_id', $document->station_id)
                    ->where('account_id', $request->account_id)
                    ->orderBy('created_at', 'DESC')
                    ->first();

                $previousAmount = $currentSiteAmount ? $currentSiteAmount->amount : 0;
                $newAmount = $previousAmount - $totalAmount;

                DB::table('site_total_ammount')->insert([
                    'station_id' => $document->station_id,
                    'account_id' => $request->account_id,
                    'amount' => $newAmount,
                    'previous_amount' => $previousAmount,
                    'date' => now(),
                    'created_at' => now()
                ]);
            }

            // If payment_status is 'paid', also insert into ammount_paid table
            if ($request->payment_status === 'paid') {
                // Check if already exists in ammount_paid table
                $existingPayment = DB::table('ammount_paid')
                    ->where('lube_id', $id)
                    ->where('type', 'debit')
                    ->first();

                // If no existing payment record, insert one
                if (!$existingPayment) {
                    DB::table('ammount_paid')->insert([
                        'lube_id' => $id,
                        'account_id' => ($request->payment_method === 'bank') ? $request->account_id : null,
                        'shift_id' => $request->shift_id,
                        'type' => 'debit',
                        'method' => $request->payment_method ?? 'cash',
                        'ammount' => $totalAmount,
                        'date' => now(),
                        'total_ammount' => $totalAmount,
                        'created_at' => now()
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Payment status updated successfully',
                'payment_status' => $request->payment_status,
                'total_amount' => $totalAmount,
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


    // ✅ 13. NEW: Process partial payment for lubes
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

        // 1. Document check karein (separately)
        $document = DB::table('lube_documents')
            ->where('id', $id)
            ->first();

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // 2. Calculate total amount
        $totalAmountResult = DB::table('lube_lines')
            ->where('document_id', $id)
            ->select(DB::raw('SUM(line_amount + tax_amount) as total_amount'))
            ->first();

        $totalAmount = $totalAmountResult ? floatval($totalAmountResult->total_amount) : 0;

        // ✅ 3. BANK PAYMENT KE LIYE BALANCE CHECK
        if ($request->payment_method === 'bank' && $request->account_id) {
            // Get current balance from site_total_ammount
            $currentBalance = DB::table('site_total_ammount')
                ->where('station_id', $document->station_id)
                ->where('account_id', $request->account_id)
                ->orderBy('created_at', 'DESC')
                ->first();

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
            $existingPayments = DB::table('ammount_paid')
                ->where('lube_id', $id)
                ->where('type', 'debit')
                ->get();

            // Calculate total paid so far
            $totalPaid = $existingPayments->sum('ammount');

            // Calculate remaining amount
            $remainingAmount = $totalAmount - $totalPaid;

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
            $previousTotal = DB::table('ammount_paid')
                ->where('lube_id', $id)
                ->where('type', $request->type)
                ->where('method', $request->payment_method)
                ->sum('ammount');

            $newTotalForMethod = $previousTotal + $newPayment;

            // ✅ 4. BANK PAYMENT KE LIYE site_total_ammount UPDATE
            $newBankBalance = null;
            if ($request->payment_method === 'bank' && $request->account_id) {
                $currentSiteAmount = DB::table('site_total_ammount')
                    ->where('station_id', $document->station_id)
                    ->where('account_id', $request->account_id)
                    ->orderBy('created_at', 'DESC')
                    ->first();

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
                DB::table('site_total_ammount')->insert([
                    'station_id' => $document->station_id,
                    'account_id' => $request->account_id,
                    'amount' => $newBankBalance,
                    'previous_amount' => $previousAmount,
                    'date' => $request->payment_date,
                    'created_at' => now()
                ]);
            }

            // Insert into ammount_paid table with custom date
            DB::table('ammount_paid')->insert([
                'lube_id' => $id,
                'account_id' => ($request->payment_method === 'bank') ? $request->account_id : null,
                'shift_id' => $request->shift_id,
                'type' => $request->type,
                'method' => $request->payment_method,
                'ammount' => $newPayment,
                'date' => $request->payment_date,
                'total_ammount' => $newTotalForMethod,
                'created_at' => now()
            ]);

            // Update total paid
            $newTotalPaid = $totalPaid + $newPayment;

            // Check if fully paid
            $finalStatus = 'partial';
            if ($newTotalPaid >= $totalAmount) {
                // Update document status to paid
                DB::table('lube_documents')
                    ->where('id', $id)
                    ->update([
                        'payment_status' => 'paid',
                        'updated_at' => now()
                    ]);
                $finalStatus = 'paid';
            }

            DB::commit();
            return response()->json([
                'message' => 'Payment recorded successfully',
                'payment_status' => $finalStatus,
                'amount_paid' => $newPayment,
                'total_paid' => $newTotalPaid,
                'remaining_amount' => $totalAmount - $newTotalPaid,
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

    // ✅ 14. NEW: Get payment history for a lube document
    public function getPaymentHistory($lubeId)
    {
        try {
            // First check if document exists
            $document = DB::table('lube_documents')->where('id', $lubeId)->first();
            if (!$document) {
                return response()->json([
                    'message' => 'Document not found',
                    'payments' => [],
                    'totals' => []
                ]);
            }

            // Get all payments for this document
            $payments = DB::table('ammount_paid as ap')
                ->join('accounts as a', function ($join) {
                    $join->on('ap.account_id', '=', 'a.id')
                        ->orOn('ap.account_id', '=', 'a.stationrow_id');
                })

                ->leftJoin('shifts as sh', 'sh.id', '=', 'ap.shift_id')
                ->select('ap.*', 'a.name as account_name', 'a.bank_name', 'a.account_number', 'sh.shift_no')
                ->where('ap.lube_id', $lubeId)
                ->orderBy('ap.created_at', 'ASC')
                ->get();

            // Calculate totals
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

            // Calculate net balance
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

    public function getInventory(Request $request)
    {
        try {
            $stationId = $request->get('station_id');

            $query = DB::table('lube_inventory as i')
                ->join('products as p', 'i.product_id', '=', 'p.id')
                ->select(
                    'i.product_id',
                    'p.name as product_name',
                    'p.category',
                    'i.quantity as current_stock',
                    'i.avg_buying_price',
                    'i.total_purchased',
                    'i.total_sold',
                    'i.last_updated as last_purchase_date',
                    DB::raw("'Packs' as unit")
                )
                ->where('p.category', 'lubricants');

            if ($stationId) {
                $query->where('i.station_id', $stationId);
            }

            $inventory = $query->orderBy('p.name')->get();

            // If no inventory records exist, return empty array
            if ($inventory->isEmpty()) {
                return response()->json([]);
            }

            // Add status badge info
            foreach ($inventory as $item) {
                $item->status = $item->current_stock <= 0 ? 'Out of Stock' :
                    ($item->current_stock < 50 ? 'Low Stock' : 'In Stock');
            }

            return response()->json($inventory);

        } catch (\Exception $e) {
            Log::error('Inventory Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Create or update inventory from initial setup
     * POST /api/lubes/inventory/setup
     */
    public function setupInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|integer|exists:stations,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'buying_price' => 'required|numeric|min:0',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $product = DB::table('products')
                ->where('id', $request->product_id)
                ->where('category', 'lubricants')
                ->first();

            if (!$product) {
                return response()->json(['error' => 'Product must be from lubricants category'], 400);
            }

            $existing = DB::table('lube_inventory')
                ->where('station_id', $request->station_id)
                ->where('product_id', $request->product_id)
                ->first();

            $oldQuantity = 0;
            $oldPrice = 0;
            $newQuantity = 0;
            $newPrice = 0;
            $inventoryId = null;

            if ($existing) {
                $oldQuantity = $existing->quantity;
                $oldPrice = $existing->avg_buying_price;

                // ✅ FIX: Sirf quantity add karo, price user ki le lo
                $newQuantity = $existing->quantity + $request->quantity;
                $newPrice = $request->buying_price;  // ✅ USER JO PRICE DALEGA WOHI

                DB::table('lube_inventory')
                    ->where('id', $existing->id)
                    ->update([
                        'quantity' => $newQuantity,
                        'avg_buying_price' => $request->buying_price,  // ✅ USER PRICE
                        'total_purchased' => $existing->total_purchased + $request->quantity,
                        'last_updated' => $request->date,
                        'updated_at' => now()
                    ]);

                $inventoryId = $existing->id;
            } else {
                $newQuantity = $request->quantity;
                $newPrice = $request->buying_price;

                $inventoryId = DB::table('lube_inventory')->insertGetId([
                    'station_id' => $request->station_id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                    'avg_buying_price' => $request->buying_price,
                    'total_purchased' => $request->quantity,
                    'total_sold' => 0,
                    'last_updated' => $request->date,
                    'created_by' => auth()->id(),
                    'stationrow_id' => $request->station_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::table('lube_inventory_logs')->insert([
                'inventory_id' => $inventoryId,
                'station_id' => $request->station_id,
                'product_id' => $request->product_id,
                'change_type' => 'initial_setup',
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'quantity_changed' => $request->quantity,
                'created_by' => auth()->id(),
                'created_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'message' => $existing ? 'Inventory updated successfully' : 'Inventory created successfully',
                'inventory' => [
                    'product_name' => $product->name,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'added_quantity' => $request->quantity,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to setup inventory: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to setup inventory: ' . $e->getMessage()], 500);
        }
    }



    /**
     * ✅ Update inventory after purchase (call this from store method)
     */
    private function updateInventoryAfterPurchase($documentId)
    {
        try {
            $document = DB::table('lube_documents')->where('id', $documentId)->first();
            if (!$document || $document->doc_type !== 'purchase') {
                return;
            }

            $lines = DB::table('lube_lines')
                ->where('document_id', $documentId)
                ->get();

            foreach ($lines as $line) {
                // Check if inventory exists
                $existing = DB::table('lube_inventory')
                    ->where('station_id', $document->station_id)
                    ->where('product_id', $line->product_id)
                    ->first();

                $oldQuantity = 0;
                $oldPrice = 0;
                $newQuantity = 0;
                $newPrice = 0;
                $inventoryId = null;

                if ($existing) {
                    $oldQuantity = $existing->quantity;
                    $oldPrice = $existing->avg_buying_price;

                    // ✅ FIX: NO AVERAGE - Sirf quantity add karo, price update mat karo
                    // Price wohi rahegi jo user ne input ki hai
                    $newQuantity = $existing->quantity + $line->qty;
                    $newPrice = $line->unit_price;  // ✅ USER JO PRICE DALEGA WOHI RAKHO

                    DB::table('lube_inventory')
                        ->where('id', $existing->id)
                        ->update([
                            'quantity' => $newQuantity,
                            'avg_buying_price' => $line->unit_price,  // ✅ USER PRICE
                            'total_purchased' => $existing->total_purchased + $line->qty,
                            'last_updated' => $document->date,
                            'updated_at' => now()
                        ]);

                    $inventoryId = $existing->id;
                } else {
                    // New product inventory
                    $newQuantity = $line->qty;
                    $newPrice = $line->unit_price;

                    $inventoryId = DB::table('lube_inventory')->insertGetId([
                        'station_id' => $document->station_id,
                        'product_id' => $line->product_id,
                        'quantity' => $line->qty,
                        'avg_buying_price' => $line->unit_price,  // ✅ USER PRICE
                        'total_purchased' => $line->qty,
                        'total_sold' => 0,
                        'last_updated' => $document->date,
                        'created_by' => $document->created_by,
                        'stationrow_id' => $document->station_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Insert log
                DB::table('lube_inventory_logs')->insert([
                    'inventory_id' => $inventoryId,
                    'station_id' => $document->station_id,
                    'product_id' => $line->product_id,
                    'change_type' => 'purchase',
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'quantity_changed' => $line->qty,
                    'document_id' => $documentId,
                    'created_by' => $document->created_by,
                    'created_at' => now()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update inventory after purchase: ' . $e->getMessage());
        }
    }


    /**
     * ✅ Update inventory after sale
     */
    private function updateInventoryAfterSale($documentId)
    {
        try {
            $document = DB::table('lube_documents')->where('id', $documentId)->first();
            if (!$document || $document->doc_type !== 'sale') {
                return;
            }

            $lines = DB::table('lube_lines')
                ->where('document_id', $documentId)
                ->get();

            foreach ($lines as $line) {
                $existing = DB::table('lube_inventory')
                    ->where('station_id', $document->station_id)
                    ->where('product_id', $line->product_id)
                    ->first();

                if ($existing) {
                    $oldQuantity = $existing->quantity;
                    $newQuantity = $existing->quantity - $line->qty;

                    DB::table('lube_inventory')
                        ->where('id', $existing->id)
                        ->update([
                            'quantity' => $newQuantity,
                            'total_sold' => $existing->total_sold + $line->qty,
                            'last_updated' => $document->date,
                            'updated_at' => now()
                        ]);

                    // Insert log
                    DB::table('lube_inventory_logs')->insert([
                        'inventory_id' => $existing->id,
                        'station_id' => $document->station_id,
                        'product_id' => $line->product_id,
                        'change_type' => 'sale',
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'old_price' => $existing->avg_buying_price,
                        'new_price' => $existing->avg_buying_price,
                        'quantity_changed' => $line->qty,
                        'document_id' => $documentId,
                        'created_by' => $document->created_by,
                        'created_at' => now()
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to update inventory after sale: ' . $e->getMessage());
        }
    }


    /**
     * ✅ Check Stock Availability - Before making a sale
     * 
     * POST /api/lubes/check-stock
     * Body: { product_id: 1, station_id: 1, quantity: 10 }
     */
public function checkStock(Request $request)
{
    $validator = Validator::make($request->all(), [
        'product_id' => 'required|integer|exists:products,id',
        'station_id' => 'required|integer|exists:stations,id',
        'quantity' => 'required|numeric|min:0.01'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // ✅ Get current stock from lube_inventory table
        $inventory = DB::table('lube_inventory')
            ->where('station_id', $request->station_id)
            ->where('product_id', $request->product_id)
            ->first();

        $currentStock = $inventory ? floatval($inventory->quantity) : 0;
        $requestedQty = floatval($request->quantity);

        // Get product details
        $product = DB::table('products')
            ->where('id', $request->product_id)
            ->first();

        $isAvailable = $currentStock >= $requestedQty;

        return response()->json([
            'available' => $isAvailable,
            'current_stock' => $currentStock,
            'requested_quantity' => $requestedQty,
            'product_id' => $request->product_id,
            'product_name' => $product ? $product->name : 'Unknown',
            'station_id' => $request->station_id,
            'short_by' => $requestedQty > $currentStock ? ($requestedQty - $currentStock) : 0,
            'message' => $isAvailable ? 'Stock available' : "Only {$currentStock} packs available"
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to check stock: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to check stock',
            'message' => $e->getMessage()
        ], 500);
    }
}

}