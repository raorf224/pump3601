<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceivedAmountController extends Controller
{
    // Main view
    public function index()
    {
        return view('received-amount');
    }

    // ✅ Get data for Admin - all stations (INDIVIDUAL ROWS)
    public function getDataAdmin(Request $request)
    {
        $type = $request->get('type');

        if (!in_array($type, ['fuel', 'credit'])) {
            return response()->json(['error' => 'Invalid type parameter'], 400);
        }

        $sql = $this->getIndividualRowsSql($type);
        $sql .= " ORDER BY scf.created_at DESC";

        try {
            $data = DB::select($sql);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('getDataAdmin Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }

    // ✅ Get data for Owner (INDIVIDUAL ROWS)
    public function getDataOwner(Request $request, $user_id)
    {
        $type = $request->get('type');

        if (!in_array($type, ['fuel', 'credit'])) {
            return response()->json(['error' => 'Invalid type parameter'], 400);
        }

        $sql = $this->getIndividualRowsSql($type);
        $sql .= " AND st.user_id = ?";
        $sql .= " ORDER BY scf.created_at DESC";

        try {
            $data = DB::select($sql, [$user_id]);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('getDataOwner Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }

    // ✅ Get data for Employee (INDIVIDUAL ROWS)
    public function getDataEmployee(Request $request, $user_id)
    {
        $type = $request->get('type');

        if (!in_array($type, ['fuel', 'credit'])) {
            return response()->json(['error' => 'Invalid type parameter'], 400);
        }

        // Get employee's assigned station
        $employeeStation = DB::selectOne("SELECT station_id FROM employees WHERE user_id = ?", [$user_id]);

        if (!$employeeStation) {
            return response()->json([]);
        }

        $sql = $this->getIndividualRowsSql($type);
        $sql .= " AND st.id = ?";
        $sql .= " ORDER BY scf.created_at DESC";

        try {
            $data = DB::select($sql, [$employeeStation->station_id]);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('getDataEmployee Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }

    /**
     * ✅ SQL query that returns INDIVIDUAL rows (no grouping, no summing)
     */
    private function getIndividualRowsSql($type)
    {
        if ($type === 'fuel') {
            return "SELECT 
                        scf.id,
                        scf.shift_id,
                        scf.fuelcard as amount,  
                        COALESCE(scf.fuel_card_paid, '0') as is_paid,
                        a.id as account_id,
                        a.name as account_name,
                        a.mdr,
                        st.id as station_id,
                        st.name as station_name,
                        s.shift_no,
                        s.start_time,
                        'fuel' as payment_type
                    FROM shift_cash_flow scf
                    INNER JOIN shifts s ON scf.shift_id = s.id
                    INNER JOIN stations st ON s.station_id = st.id
                    LEFT JOIN accounts a ON scf.faccountid = a.id
                    WHERE scf.faccountid IS NOT NULL 
                      AND scf.faccountid > 0
                      AND scf.fuelcard > 0";
        } else {
            return "SELECT 
                        scf.id,
                        scf.shift_id,
                        scf.creditcard as amount, 
                        COALESCE(scf.credit_card_paid, '0') as is_paid,
                        a.id as account_id,
                        a.name as account_name,
                        a.mdr,
                        st.id as station_id,
                        st.name as station_name,
                        s.shift_no,
                        s.start_time,
                        'credit' as payment_type
                    FROM shift_cash_flow scf
                    INNER JOIN shifts s ON scf.shift_id = s.id
                    INNER JOIN stations st ON s.station_id = st.id
                    LEFT JOIN accounts a ON scf.caccountid = a.id
                    WHERE scf.caccountid IS NOT NULL 
                      AND scf.caccountid > 0
                      AND scf.creditcard > 0";
        }
    }

    /**
     * ✅ Receive Payment - Updates paid status AND inserts ONLY MDR as EXPENSE
     */
    public function receivePayment(Request $request)
    {
        $validated = $request->validate([
            'cash_flow_id' => 'required|integer|exists:shift_cash_flow,id',
            'payment_type' => 'required|in:fuel,credit',
            'shift_id' => 'required|integer|exists:shifts,id',
            'account_id' => 'required|integer|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'mdr_percentage' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            // Get the cash flow record
            $cashFlow = DB::table('shift_cash_flow')
                ->where('id', $validated['cash_flow_id'])
                ->first();

            if (!$cashFlow) {
                return response()->json(['message' => 'Record not found'], 404);
            }

            // Check if already paid
            $column = $validated['payment_type'] === 'fuel' ? 'fuel_card_paid' : 'credit_card_paid';
            if ($cashFlow->{$column} == '1') {
                return response()->json(['message' => 'Payment already received'], 400);
            }

            // Get station_id from shift
            $shift = DB::table('shifts')->where('id', $validated['shift_id'])->first();
            if (!$shift) {
                return response()->json(['message' => 'Shift not found'], 404);
            }

            // Calculate MDR amount
            $mdrPercentage = floatval($validated['mdr_percentage'] ?? 0);
            $totalAmount = floatval($validated['amount']);
            $mdrAmount = ($totalAmount * $mdrPercentage) / 100;
            $netAmount = $totalAmount - $mdrAmount;

            // ✅ ONLY INSERT MDR as EXPENSE in transactions table
            if ($mdrAmount > 0) {
                $paymentTypeText = $validated['payment_type'] === 'fuel' ? 'Fuel Card' : 'Credit Card';
                
                DB::table('transactions')->insert([
                    'station_id' => $shift->station_id,
                    'account_id' => $validated['account_id'],
                    'shift_id' => $validated['shift_id'],
                    'type' => 'expense',
                    'debit' => $mdrAmount,
                    'credit' => 0.00,
                    'method' => 'card',
                    'to_account' => null,
                    'note' => "MDR Charges ({$mdrPercentage}%) for {$paymentTypeText} payment of PKR " . number_format($totalAmount, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_testing' => 0
                ]);
            }

            // Get latest amount for this account
            $latestRecord = DB::table('site_total_ammount')
                ->where('station_id', $shift->station_id)
                ->where('account_id', $validated['account_id'])
                ->orderBy('id', 'desc')
                ->first();

            $previousAmount = $latestRecord ? floatval($latestRecord->amount) : 0;
            $newAmount = $previousAmount + $netAmount;

            // Insert record in site_total_ammount (history tracking)
            DB::table('site_total_ammount')->insert([
                'station_id' => $shift->station_id,
                'account_id' => $validated['account_id'],
                'amount' => $newAmount,
                'previous_amount' => $previousAmount,
                'date' => now(),
                'created_by' => auth()->id(),
                'created_at' => now()
            ]);

            // ✅ ONLY UPDATE the paid status - NO CHANGE to shift_id
            DB::table('shift_cash_flow')
                ->where('id', $validated['cash_flow_id'])
                ->update([
                    $column => '1',
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment received successfully',
                'mdr_percentage' => $mdrPercentage,
                'mdr_amount' => $mdrAmount,
                'total_amount' => $totalAmount,
                'net_amount' => $netAmount,
                'previous_balance' => $previousAmount,
                'new_balance' => $newAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Receive Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to receive payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get open shifts for a station
     */
    public function getOpenShifts($stationId)
    {
        $shifts = DB::table('shifts')
            ->where('station_id', $stationId)
            ->where('status', 'open')
            ->select('id', 'shift_no', 'start_time')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($shifts);
    }
}