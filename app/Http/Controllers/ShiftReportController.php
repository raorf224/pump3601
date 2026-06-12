<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Station;
use App\Models\Tank;
use App\Models\TankDip;
use App\Models\Nozzle;
use App\Models\ShiftNozzleReading;
use App\Models\NozzleTotalizerReset;
use App\Models\Dispenser;
use App\Models\Product;
use App\Models\LubeDocument;
use App\Models\LubeLine;
use App\Models\ShiftCashFlow;
use App\Models\Transaction;
use App\Models\OilPurchase;
use App\Models\Account;
use DB;

class ShiftReportController extends Controller
{
    public function index()
    {
        $stations = Station::where('status', 1)->get();
        return view('shift-reports', compact('stations'));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'station_id' => 'required|exists:stations,id',
            'shift_id' => 'required|exists:shifts,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date'
        ]);

        $stationId = $request->station_id;
        $shiftId = $request->shift_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Shift details
        $shift = Shift::with(['station', 'shiftIncharger.user'])
            ->where('id', $shiftId)
            ->where('station_id', $stationId)
            ->firstOrFail();

        // Tanks dips for this shift period
        $tankDips = TankDip::with(['tank', 'tank.product'])
            ->whereHas('tank', function ($query) use ($stationId) {
                $query->where('station_id', $stationId);
            })
            ->whereBetween('from_date', [$fromDate, $toDate])
            ->orWhereBetween('to_date', [$fromDate, $toDate])
            ->get();

        // Nozzle readings for this shift
        $nozzleReadings = ShiftNozzleReading::with(['nozzle', 'nozzle.dispenser', 'nozzle.product'])
            ->where('shift_id', $shiftId)
            ->get();

        // Nozzle totalizer resets during this shift
        $nozzleResets = NozzleTotalizerReset::with(['nozzle', 'nozzle.dispenser'])
            ->where('shift_id', $shiftId)
            ->get();

        // ✅ Lubricant Documents (Purchase & Sale)
        $lubeDocuments = LubeDocument::with(['lines', 'lines.product', 'account'])
            ->where('shift_id', $shiftId)
            ->get();

        // ✅ NEW: Oil Purchases
        $oilPurchases = OilPurchase::with(['tank', 'tank.product', 'supplier'])
            ->where('shift_id', $shiftId)
            ->get();

        // ✅ Calculate summaries
        $lubeSummary = $this->calculateLubeSummary($lubeDocuments);
        $oilPurchaseSummary = $this->calculateOilPurchases($shiftId); // ✅ NEW

        // ✅ Cash Flow Summary
        $cashFlow = ShiftCashFlow::where('shift_id', $shiftId)->first();

        // ✅ General Transactions (income/expense)
        $transactions = Transaction::with(['account', 'toAccount'])
            ->where('shift_id', $shiftId)
            ->get();

        // ✅ UPDATED: Calculate gain/loss for each tank (with new parameters)
        $tankCalculations = $this->calculateTankGainLoss(
            $tankDips,
            $nozzleReadings,
            $nozzleResets,
            $stationId,
            $shift->start_time,
            $shift->id

        );

        // ✅ Total Financial Summary (UPDATE WITH OIL PURCHASES)
        $financialSummary = $this->calculateFinancialSummary(
            $nozzleReadings,
            $nozzleResets,
            $lubeSummary,
            $oilPurchaseSummary, // ✅ NEW PARAMETER
            $transactions,
            $cashFlow,
            $cashFlow->fuelcard,
            $cashFlow->creditcard,
            $shift // ✅ ADD shift for cash handover
        );

        return view('shift-reports', compact(
            'shift',
            'tankDips',
            'nozzleReadings',
            'nozzleResets',
            'tankCalculations',
            'lubeDocuments',
            'lubeSummary',
            'oilPurchases', // ✅ NEW
            'oilPurchaseSummary', // ✅ NEW
            'cashFlow',
            'transactions',
            'financialSummary',
            'fromDate',
            'toDate'
        ));
    }

    private function calculateTankGainLoss($tankDips, $nozzleReadings, $nozzleResets, $stationId, $shiftStartTime, $shiftId)
    {
        $calculations = [];

        $stationTanks = Tank::with('product')
            ->where('station_id', $stationId)
            ->where('status', 'active')
            ->get();

        foreach ($stationTanks as $tank) {
            $tankId = $tank->id;

            // Get opening and closing
            $shiftDip = TankDip::where('tank_id', $tankId)
                ->where('shift_id', $shiftId)
                ->first();

            $openingStock = 0;
            $closingStock = 0;

            if ($shiftDip) {
                $openingStock = $shiftDip->old_dip_liters ?? 0;
                $closingStock = $shiftDip->dip_in_liters ?? 0;
            } else {
                $previousDip = TankDip::where('tank_id', $tankId)
                    ->where('to_date', '<', $shiftStartTime)
                    ->orderBy('to_date', 'DESC')
                    ->first();

                if ($previousDip) {
                    $openingStock = $previousDip->dip_in_liters ?? 0;
                }
                $closingStock = $openingStock;
            }

            // ✅ **PHYSICAL USAGE** (Opening - Closing)
            $physicalUsage = $openingStock - $closingStock;

            // ✅ **OIL PURCHASE** - Sirf us shift ka oil receive dikhao jisme actually receive hua hai
            $oilPurchases = DB::table('oil_recived_tanks as ort')
                ->join('oil_purchase as op', 'op.id', '=', 'ort.oil_purchase_id')
                ->where('op.tank_id', $tankId)
                ->where('ort.shift_id', $shiftId)  // ✅ RECEIVE SHIFT ID MATCH

                ->sum('ort.recived_qty');

            \Log::info("Tank {$tank->name} - Shift {$shiftId} - Oil Purchased (Received): {$oilPurchases}");


            // ✅ **ADJUSTED PHYSICAL USAGE**
            $adjustedPhysicalUsage = $physicalUsage + $oilPurchases;

            // ✅ **NOZZLE SALES**
            $tankNozzles = Nozzle::where('tank_id', $tankId)->pluck('id');
            $nozzleSalesLiters = $nozzleReadings->whereIn('nozzle_id', $tankNozzles)->sum('total_dispensed');
            $resetSalesLiters = $nozzleResets->whereIn('nozzle_id', $tankNozzles)->sum('total_dispensed');
            $totalNozzleSales = $nozzleSalesLiters + $resetSalesLiters;

            // ✅ **VARIANCE CALCULATION**
            $variance = $totalNozzleSales - $adjustedPhysicalUsage;

            // ✅ **PERCENTAGE VARIANCE**
            $variancePercent = 0;
            $showVariancePercent = true;

            if ($totalNozzleSales > 0) {
                $variancePercent = ($variance / $totalNozzleSales) * 100;
            } elseif ($adjustedPhysicalUsage > 0) {
                $variancePercent = ($variance / $adjustedPhysicalUsage) * 100;
            } else {
                $showVariancePercent = false;
                $variancePercent = 0;
            }

            // ✅ **STATUS DETERMINATION (IMPROVED)**
            $status = '';
            $statusClass = '';

            if ($totalNozzleSales > 0) {
                if (abs($variancePercent) <= 0.5) {
                    $status = 'Normal';
                    $statusClass = 'success';
                } elseif (abs($variancePercent) <= 1.0) {
                    $status = 'Warning';
                    $statusClass = 'warning';
                } else {
                    $status = 'Critical';
                    $statusClass = 'danger';
                }
            } else {
                // Agar sales zero hai
                if (abs($variance) > 0) {
                    $status = 'Warning';
                    $statusClass = 'warning';
                } else {
                    $status = 'Normal';
                    $statusClass = 'success';
                }
            }

            // ✅ **GAIN/LOSS DETERMINATION** (BASED ON CORRECTED VARIANCE)
            $gainLoss = '';
            $gainLossClass = '';

            if (abs($variance) < 0.01) {
                $gainLoss = 'BALANCED';
                $gainLossClass = 'secondary';
            } elseif ($variance > 0) {
                $gainLoss = 'GAIN';
                $gainLossClass = 'success';
            } else {
                $gainLoss = 'LOSS';
                $gainLossClass = 'danger';
            }

            // ✅ **TEXT DESCRIPTION**
            $varianceText = '';

            if ($physicalUsage > 0) {
                $varianceText = "📉 Stock DECREASED by " . number_format($physicalUsage, 2) . " L";
                if ($totalNozzleSales > 0) {
                    $varianceText .= " | Sales: " . number_format($totalNozzleSales, 2) . " L";
                }
            } elseif ($physicalUsage < 0) {
                $varianceText = "📈 Stock INCREASED by " . number_format(abs($physicalUsage), 2) . " L";
                if ($totalNozzleSales > 0) {
                    $varianceText .= " | Sales: " . number_format($totalNozzleSales, 2) . " L";
                }
            } else {
                $varianceText = "⚖️ No change in stock level";
            }

            $calculations[$tankId] = [
                'tank_name' => $tank->name,
                'product_name' => $tank->product->name ?? 'Unknown',
                'opening_stock' => $openingStock,
                'closing_stock' => $closingStock,
                'physical_usage' => $physicalUsage,
                'oil_purchased' => $oilPurchases,
                'adjusted_physical_usage' => $adjustedPhysicalUsage,
                'nozzle_sales_liters' => $nozzleSalesLiters,
                'reset_sales_liters' => $resetSalesLiters,
                'total_nozzle_sales' => $totalNozzleSales,
                'variance' => $variance,
                'variance_percent' => $variancePercent,
                'show_variance_percent' => $showVariancePercent,
                'gain_loss' => $gainLoss,
                'gain_loss_class' => $gainLossClass,
                'status' => $status,
                'status_class' => $statusClass,
                'variance_text' => $varianceText,
            ];

        }

        return $calculations;
    }

    // ✅ NEW: Calculate Lube Summary
    // ✅ FIXED: Calculate Lube Summary - ONLY PAID
    private function calculateLubeSummary($lubeDocuments)
    {
        $summary = [
            'purchase' => [
                'total_qty' => 0,
                'total_amount' => 0,
                'cash_paid' => 0,
                'bank_paid' => 0,
                'card_paid' => 0,
                'credit' => 0,
                'documents' => []
            ],
            'sale' => [
                'total_qty' => 0,
                'total_amount' => 0,
                'cash_received' => 0,
                'bank_received' => 0,
                'card_received' => 0,
                'credit' => 0,
                'documents' => []
            ]
        ];

        foreach ($lubeDocuments as $document) {
            $type = $document->doc_type; // 'purchase' or 'sale'

            $totalQty = $document->lines->sum('qty');
            $totalAmount = $document->lines->sum('line_amount');

            // ✅ ONLY COUNT IF PAYMENT STATUS IS 'PAID'
            if ($document->payment_status == 'paid') {
                $summary[$type]['total_qty'] += $totalQty;
                $summary[$type]['total_amount'] += $totalAmount;

                // Payment breakdown
                if ($document->payment_method == 'cash') {
                    $summary[$type][$type == 'purchase' ? 'cash_paid' : 'cash_received'] += $totalAmount;
                } elseif ($document->payment_method == 'bank') {
                    $summary[$type][$type == 'purchase' ? 'bank_paid' : 'bank_received'] += $totalAmount;
                } elseif ($document->payment_method == 'card') {
                    $summary[$type][$type == 'purchase' ? 'card_paid' : 'card_received'] += $totalAmount;
                }
            } elseif (in_array($document->payment_status, ['credit', 'not_paid', 'partial'])) {
                // ✅ Credit transactions are NOT included in financial summary
                $summary[$type]['credit'] += $totalAmount;
            }

            // ✅ But we still keep the document for display
            $summary[$type]['documents'][] = $document;
        }

        return $summary;
    }

    // ✅ NEW: Calculate Transactions Summary
    private function calculateTransactionsSummary($transactions)
    {
        $summary = [
            'income' => [
                'total' => 0,
                'cash' => 0,
                'bank' => 0,
                'card' => 0,
                'credit' => 0
            ],
            'expense' => [
                'total' => 0,
                'cash' => 0,
                'bank' => 0,
                'card' => 0,
                'credit' => 0
            ]
        ];

        foreach ($transactions as $transaction) {
            $type = $transaction->type; // 'income' or 'expense'
            $amount = $type === 'income' ? $transaction->credit : $transaction->debit;

            $summary[$type]['total'] += $amount;

            // Payment method breakdown
            if ($transaction->method === 'cash') {
                $summary[$type]['cash'] += $amount;
            } elseif ($transaction->method === 'bank') {
                $summary[$type]['bank'] += $amount;
            } elseif ($transaction->method === 'card') {
                $summary[$type]['card'] += $amount;
            } elseif ($transaction->method === 'credit') {
                $summary[$type]['credit'] += $amount;
            }
        }

        return $summary;
    }

    // ✅ NEW: Calculate Oil Purchases Summary
    private function calculateOilPurchases($shiftId)
    {
        // Get all paid amounts from ammount_paid table for this shift
        $paidAmounts = DB::table('ammount_paid')
            ->where('shift_id', $shiftId)
            ->where('type', 'debit')
            ->where('method', 'cash')
            ->get();

        $summary = [
            'total_qty' => 0,
            'total_amount' => 0,
            'cash_paid' => 0,
            'bank_paid' => 0,
            'card_paid' => 0,
            'credit' => 0,
            'count' => 0,
            'purchases' => []
        ];

        // Group payments by oil_purchase_id
        $paymentsByPurchase = [];
        foreach ($paidAmounts as $payment) {
            if ($payment->oil_purchase_id) {
                if (!isset($paymentsByPurchase[$payment->oil_purchase_id])) {
                    $paymentsByPurchase[$payment->oil_purchase_id] = 0;
                }
                $paymentsByPurchase[$payment->oil_purchase_id] += $payment->ammount;
            }
        }

        // Get oil purchases with their paid amounts
        $oilPurchases = OilPurchase::with(['tank', 'tank.product', 'supplier'])
            ->where('shift_id', $shiftId)
            ->get();

        foreach ($oilPurchases as $purchase) {
            $receivedQty = $purchase->recieved_qty ?? 0;
            $rate = $purchase->rate ?? 0;
            $calculatedAmount = $receivedQty * $rate;

            // Get actual paid amount from payments
            $actualPaidAmount = $paymentsByPurchase[$purchase->id] ?? 0;

            // Determine payment status based on actual payments
            $paymentStatus = 'not_paid';
            if ($actualPaidAmount >= $calculatedAmount && $calculatedAmount > 0) {
                $paymentStatus = 'paid';
            } elseif ($actualPaidAmount > 0) {
                $paymentStatus = 'partial';
            }

            // ONLY COUNT PAID OR PARTIAL PURCHASES FOR FINANCIAL SUMMARY
            if ($paymentStatus == 'paid' || $paymentStatus == 'partial') {
                $summary['total_qty'] += $receivedQty;
                $summary['total_amount'] += $actualPaidAmount; // ✅ USE ACTUAL PAID AMOUNT
                $summary['count']++;

                // Payment breakdown
                $summary['cash_paid'] += $actualPaidAmount;
            } else {
                $summary['credit'] += $calculatedAmount;
            }

            $summary['purchases'][] = [
                'id' => $purchase->id,
                'invoice_no' => $purchase->invoice_no,
                'product_name' => $purchase->tank->product->name ?? 'Oil Purchase',
                'qty' => $receivedQty,
                'rate' => $rate,
                'amount' => $actualPaidAmount, // ✅ ACTUAL PAID AMOUNT
                'calculated_amount' => $calculatedAmount,
                'payment_status' => $paymentStatus,
                'payment_method' => $actualPaidAmount > 0 ? 'cash' : 'credit',
                'recieving_date' => $purchase->recieving_date
            ];
        }

        return $summary;
    }


    // ✅ NEW: Calculate Total Financial Summary 
    private function calculateFinancialSummary($nozzleReadings, $nozzleResets, $lubeSummary, $oilPurchaseSummary, $transactions, $cashFlow, $shift = null)
    {
        // Fuel Sales (always considered paid since cash)
        $fuelSales = $nozzleReadings->sum('total_amount') + $nozzleResets->sum('total_amount');

        // ✅ OIL PURCHASE TOTAL - ONLY PAID
        $oilPurchaseTotal = $oilPurchaseSummary['total_amount'];

        // ✅ Lube Net Sales - ONLY PAID (sale - purchase)
        $lubeNet = $lubeSummary['sale']['total_amount'] - $lubeSummary['purchase']['total_amount'];

        // ✅ Transactions Summary - ONLY CASH (paid)
        $transactionsSummary = $this->calculateTransactionsSummary($transactions);

        // Total Income from transactions - ONLY CASH
        $transactionIncome = $transactionsSummary['income']['total'];

        // Total Expense from transactions - ONLY CASH
        $transactionExpense = $transactionsSummary['expense']['total'];

        // Transaction Net
        $transactionNet = $transactionIncome - $transactionExpense;

        // ✅ UPDATED: Total Revenue (Fuel + Lube Sale(paid only) + Transaction Income(paid only))
        $totalRevenue = $fuelSales + $lubeSummary['sale']['total_amount'] + $transactionIncome;

        // ✅ UPDATED: Total Expenses (Lube Purchase(paid only) + Transaction Expense(paid only) + Oil Purchase(paid only))
        $totalExpenses = $lubeSummary['purchase']['total_amount'] + $transactionExpense + $oilPurchaseTotal;

        // ✅ UPDATED: Net Income (Revenue - Expenses)
        $netIncome = $totalRevenue - $totalExpenses;

        // Cash Position from cash flow
        $cashInHand = $cashFlow ? $cashFlow->in_hand : 0;
        $cashInBank = $cashFlow ? $cashFlow->in_bank : 0;

        // ✅ CASH HANDOVER from shift (opening cash)
        $cashHandover = $shift ? $shift->cash_handover : 0;

        return [
            // Sales
            'fuel_sales' => $fuelSales,
            'lube_sales' => $lubeSummary['sale']['total_amount'], // ONLY PAID
            'lube_purchase' => $lubeSummary['purchase']['total_amount'], // ONLY PAID
            'lube_net' => $lubeNet,

            // ✅ NEW: Oil Purchase (ONLY PAID)
            'oil_purchase' => $oilPurchaseTotal,

            // Transactions (ONLY CASH)
            'transaction_income' => $transactionIncome,
            'transaction_expense' => $transactionExpense,
            'transaction_net' => $transactionNet,
            'transaction_summary' => $transactionsSummary,

            // Totals
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,

            // Cash
            'cash_handover' => $cashHandover,
            'cash_in_hand' => $cashInHand,
            'cash_in_bank' => $cashInBank,
            // ✅ FIXED: Total Cash Balance should NOT include cash handover twice
            'total_cash_balance' => $cashInHand + $cashInBank // NOT + $cashHandover
        ];
    }
    public function show($shiftId)
    {
        $shift = Shift::with(['station', 'shiftIncharger.user'])
            ->where('id', $shiftId)
            ->firstOrFail();

        // Automatically get tank dips for shift period
        $tankDips = TankDip::with(['tank', 'tank.product'])
            ->whereHas('tank', function ($query) use ($shift) {
                $query->where('station_id', $shift->station_id);
            })
            ->whereBetween('from_date', [$shift->start_time, $shift->end_time])
            ->orWhereBetween('to_date', [$shift->start_time, $shift->end_time])
            ->get();

        // ✅ ADD DEBUG INFO
        foreach ($tankDips as $dip) {
            \Log::info("TankDip ID {$dip->id}: Tank={$dip->tank_id}, Old={$dip->old_dip_liters}, New={$dip->dip_in_liters}");
        }

        $nozzleReadings = ShiftNozzleReading::with(['nozzle', 'nozzle.dispenser', 'nozzle.product'])
            ->where('shift_id', $shiftId)
            ->get();

        $nozzleResets = NozzleTotalizerReset::with(['nozzle', 'nozzle.dispenser'])
            ->where('shift_id', $shiftId)
            ->get();

        // ✅ Lubricant Documents
        $lubeDocuments = LubeDocument::with(['lines', 'lines.product', 'account'])
            ->where('shift_id', $shiftId)
            ->get();

        // ✅ NEW: Oil Purchases
        $oilPurchases = OilPurchase::with(['tank', 'tank.product', 'supplier'])
            ->where('shift_id', $shiftId)
            ->get();

        // ✅ Calculate summaries
        $lubeSummary = $this->calculateLubeSummary($lubeDocuments);
        $oilPurchaseSummary = $this->calculateOilPurchases($shiftId); // ✅ NEW

        // ✅ Cash Flow Summary
        $cashFlow = ShiftCashFlow::where('shift_id', $shiftId)->first();


        // ✅ General Transactions
        $transactions = Transaction::with(['account', 'toAccount'])
            ->where('shift_id', $shiftId)
            ->get();

        // ✅ FIXED: All 5 parameters pass karo
        $tankCalculations = $this->calculateTankGainLoss(
            $tankDips,
            $nozzleReadings,
            $nozzleResets,
            $shift->station_id, // ✅ Station ID
            $shift->start_time, // ✅ Shift start time
            $shift->id // ✅ Shift ID pass karo

        );

        // ✅ Total Financial Summary (UPDATE WITH OIL PURCHASES)
        $financialSummary = $this->calculateFinancialSummary(
            $nozzleReadings,
            $nozzleResets,
            $lubeSummary,
            $oilPurchaseSummary, // ✅ NEW PARAMETER
            $transactions,
            $cashFlow,
            $shift // ✅ ADD shift parameter
        );

        return view('shift-reports', compact(
            'shift',
            'tankDips',
            'nozzleReadings',
            'nozzleResets',
            'tankCalculations',
            'lubeDocuments',
            'lubeSummary',
            'oilPurchases', // ✅ NEW
            'oilPurchaseSummary', // ✅ NEW
            'cashFlow',
            'transactions',
            'financialSummary'
        ));
    }

  public function downloadPDF($shiftId)
    {
        $shift = Shift::with(['station', 'shiftIncharger.user'])
            ->where('id', $shiftId)
            ->firstOrFail();

        $tankDips = TankDip::with(['tank', 'tank.product'])
            ->whereHas('tank', function ($query) use ($shift) {
                $query->where('station_id', $shift->station_id);
            })
            ->whereBetween('from_date', [$shift->start_time, $shift->end_time])
            ->orWhereBetween('to_date', [$shift->start_time, $shift->end_time])
            ->get();

        $nozzleReadings = ShiftNozzleReading::with(['nozzle', 'nozzle.dispenser', 'nozzle.product'])
            ->where('shift_id', $shiftId)
            ->get();

        $nozzleResets = NozzleTotalizerReset::with(['nozzle', 'nozzle.dispenser'])
            ->where('shift_id', $shiftId)
            ->get();

        $lubeDocuments = LubeDocument::with(['lines', 'lines.product', 'account'])
            ->where('shift_id', $shiftId)
            ->get();

        $oilPurchases = OilPurchase::with(['tank', 'tank.product', 'supplier'])
            ->where('shift_id', $shiftId)
            ->get();

        $lubeSummary = $this->calculateLubeSummary($lubeDocuments);
        $oilPurchaseSummary = $this->calculateOilPurchases($shiftId);
        $cashFlow = ShiftCashFlow::where('shift_id', $shiftId)->first();
        $transactions = Transaction::with(['account', 'toAccount'])
            ->where('shift_id', $shiftId)
            ->get();

        $tankCalculations = $this->calculateTankGainLoss(
            $tankDips,
            $nozzleReadings,
            $nozzleResets,
            $shift->station_id,
            $shift->start_time,
            $shift->id
        );

        $financialSummary = $this->calculateFinancialSummary(
            $nozzleReadings,
            $nozzleResets,
            $lubeSummary,
            $oilPurchaseSummary,
            $transactions,
            $cashFlow,
            $shift
        );

        $pdfFileName = 'shift_stock_report_SHIFT-' . $shift->id . '_'
            . date('Y-m-d', strtotime($shift->start_time))
            . '_to_'
            . date('Y-m-d', strtotime($shift->end_time ?? $shift->start_time));

        return view('pdf_download', compact(
            'shift',
            'tankCalculations',
            'nozzleReadings',
            'nozzleResets',
            'tankDips',
            'financialSummary',
            'cashFlow',
            'lubeDocuments',
            'lubeSummary',
            'oilPurchases',
            'oilPurchaseSummary',
            'transactions',
            'pdfFileName'
        ));
    }


}