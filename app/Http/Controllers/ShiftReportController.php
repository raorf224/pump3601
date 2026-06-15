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
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
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
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $shift = Shift::with(['station', 'shiftIncharger.user'])->findOrFail($shiftId);

        // Fetch all required data
        $tankDips = TankDip::with(['tank', 'tank.product'])
            ->whereHas('tank', fn($q) => $q->where('station_id', $shift->station_id))
            ->where('shift_id', $shiftId)->get();

        $nozzleReadings = ShiftNozzleReading::with(['nozzle', 'nozzle.dispenser', 'nozzle.product'])
            ->where('shift_id', $shiftId)->get();

        $nozzleResets = NozzleTotalizerReset::with(['nozzle', 'nozzle.dispenser'])
            ->where('shift_id', $shiftId)->get();

        $lubeDocuments = LubeDocument::with(['lines', 'lines.product', 'account'])
            ->where('shift_id', $shiftId)->get();

        $oilPurchases = OilPurchase::with(['tank', 'tank.product', 'supplier'])
            ->where('shift_id', $shiftId)->get();

        $lubeSummary = $this->calculateLubeSummary($lubeDocuments);
        $oilPurchaseSummary = $this->calculateOilPurchases($shiftId);
        $cashFlow = ShiftCashFlow::where('shift_id', $shiftId)->first();
        $transactions = Transaction::with(['account', 'toAccount'])->where('shift_id', $shiftId)->get();

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

        $data = compact(
            'shift',
            'tankCalculations',
            'nozzleReadings',
            'nozzleResets',
            'financialSummary',
            'cashFlow',
            'lubeDocuments',
            'lubeSummary',
            'oilPurchases',
            'oilPurchaseSummary',
            'transactions'
        );

        // Return view with auto-download option
        // Add ?auto=true to URL to auto-download PDF
        return view('pdf_download', $data);

    }


    public function exportToExcel($shiftId)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        
        $shift = Shift::with(['station', 'shiftIncharger.user'])->findOrFail($shiftId);
        
        // Fetch all data
        $tankDips = TankDip::with(['tank', 'tank.product'])
            ->whereHas('tank', fn($q) => $q->where('station_id', $shift->station_id))
            ->where('shift_id', $shiftId)->get();

        $nozzleReadings = ShiftNozzleReading::with(['nozzle', 'nozzle.dispenser', 'nozzle.product'])
            ->where('shift_id', $shiftId)->get();

        $nozzleResets = NozzleTotalizerReset::with(['nozzle', 'nozzle.dispenser'])
            ->where('shift_id', $shiftId)->get();

        $lubeDocuments = LubeDocument::with(['lines', 'lines.product', 'account'])
            ->where('shift_id', $shiftId)->get();

        $oilPurchases = OilPurchase::with(['tank', 'tank.product', 'supplier'])
            ->where('shift_id', $shiftId)->get();

        $lubeSummary = $this->calculateLubeSummary($lubeDocuments);
        $oilPurchaseSummary = $this->calculateOilPurchases($shiftId);
        $cashFlow = ShiftCashFlow::where('shift_id', $shiftId)->first();
        $transactions = Transaction::with(['account', 'toAccount'])->where('shift_id', $shiftId)->get();

        $tankCalculations = $this->calculateTankGainLoss(
            $tankDips, $nozzleReadings, $nozzleResets, 
            $shift->station_id, $shift->start_time, $shift->id
        );

        $financialSummary = $this->calculateFinancialSummary(
            $nozzleReadings, $nozzleResets, $lubeSummary, 
            $oilPurchaseSummary, $transactions, $cashFlow, $shift
        );

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Shift Stock Report');
        
        // Set page layout to Landscape A4
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageMargins()->setTop(0.75);
        $sheet->getPageMargins()->setBottom(0.75);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        
        $row = 1;
        
        // ========================================
        // HEADER SECTION (EXACT MATCH)
        // ========================================
        
        // Company Header
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->setCellValue('A' . $row, 'Shift Stock Reconciliation Report');
        $sheet->getStyle('A' . $row)->getFont()->setSize(18)->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->setCellValue('A' . $row, $shift->station->name ?? 'Fuel Station');
        $sheet->getStyle('A' . $row)->getFont()->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->setCellValue('A' . $row, 'Professional Fuel Management System');
        $sheet->getStyle('A' . $row)->getFont()->setSize(10);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        // Report ID and Time
        $sheet->setCellValue('A' . $row, 'Report ID: SHIFT-' . $shift->id);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(9);
        
        $sheet->setCellValue('J' . $row, 'Generated: ' . now()->format('M d, Y H:i'));
        $sheet->mergeCells('J' . $row . ':M' . $row);
        $sheet->getStyle('J' . $row)->getFont()->setSize(9);
        $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 2;
        
        // ========================================
        // SHIFT OVERVIEW CARD
        // ========================================
        $sheet->setCellValue('A' . $row, 'Shift Overview');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');
        $sheet->getStyle('A' . $row)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $row++;
        
        // Shift info in rows (as in your design)
        $infoData = [
            ['Station:', $shift->station->name ?? 'N/A'],
            ['Shift Type:', $shift->shift_no == 1 ? 'Day Shift' : 'Night Shift'],
            ['Shift Incharge:', $shift->shiftIncharger->user->full_name ?? 'N/A'],
            ['Opening Balance:', 'Rs. ' . number_format($shift->cash_handover ?? 0, 2)],
            ['Status:', ucfirst($shift->status ?? 'Closed')]
        ];
        
        foreach ($infoData as $index => $data) {
            $col = 65 + ($index * 2); // A, C, E, G, I
            $labelCell = chr($col) . $row;
            $valueCell = chr($col + 1) . $row;
            
            $sheet->setCellValue($labelCell, $data[0]);
            $sheet->getStyle($labelCell)->getFont()->setBold(true);
            $sheet->getStyle($labelCell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
            $sheet->setCellValue($valueCell, $data[1]);
            $sheet->getStyle($valueCell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
        $row++;
        
        // Time row
        $sheet->setCellValue('A' . $row, 'Start Time:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($shift->start_time)->format('M d, Y H:i'));
        
        $sheet->setCellValue('C' . $row, 'End Time:');
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('D' . $row, $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('M d, Y H:i') : 'Not Ended');
        $row += 2;
        
        // ========================================
        // PROFESSIONAL STOCK ANALYSIS TABLE
        // ========================================
        $sheet->setCellValue('A' . $row, 'Professional Stock Analysis');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');
        $sheet->getStyle('A' . $row)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $row++;
        
        // Table Headers (exactly as in your design)
        $headers = ['Tank', 'Product', 'Opening (L)', 'Closing (L)', 'Oil Received (L)', 'Nozzle Sales (L)', 'Variance (L)', 'Variance %', 'Status'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        $row++;
        
        // Table Data
        foreach ($tankCalculations as $calc) {
            $sheet->setCellValue('A' . $row, $calc['tank_name']);
            $sheet->setCellValue('B' . $row, $calc['product_name']);
            $sheet->setCellValue('C' . $row, number_format($calc['opening_stock'], 2));
            $sheet->setCellValue('D' . $row, number_format($calc['closing_stock'], 2));
            $sheet->setCellValue('E' . $row, number_format($calc['oil_purchased'], 2));
            $sheet->setCellValue('F' . $row, number_format($calc['total_nozzle_sales'], 2));
            $sheet->setCellValue('G' . $row, ($calc['variance'] > 0 ? '+' : '') . number_format($calc['variance'], 2));
            $sheet->setCellValue('H' . $row, $calc['total_nozzle_sales'] > 0 ? number_format($calc['variance_percent'], 2) . '%' : 'N/A');
            $sheet->setCellValue('I' . $row, $calc['status']);
            
            // Style variance
            if ($calc['variance'] > 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setARGB('FF28a745');
                $sheet->getStyle('G' . $row)->getFont()->setBold(true);
            } elseif ($calc['variance'] < 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setARGB('FFdc3545');
                $sheet->getStyle('G' . $row)->getFont()->setBold(true);
            }
            
            // Style status
            if ($calc['status'] == 'Gain') {
                $sheet->getStyle('I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFd4edda');
            } elseif ($calc['status'] == 'Loss') {
                $sheet->getStyle('I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFf8d7da');
            }
            
            // Apply borders
            for ($col = 0; $col <= 8; $col++) {
                $cell = chr(65 + $col) . $row;
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
            $row++;
        }
        $row += 2;
        
        // ========================================
        // TANK-WISE DETAILED ANALYSIS
        // ========================================
        $sheet->setCellValue('A' . $row, 'Tank-wise Detailed Analysis');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');
        $sheet->getStyle('A' . $row)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $row++;
        
        foreach ($tankCalculations as $tankId => $calc) {
            // Tank Title
            $sheet->setCellValue('A' . $row, $calc['tank_name'] . ' - ' . $calc['product_name']);
            $sheet->mergeCells('A' . $row . ':M' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF34495e');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $row++;
            
            // Two column layout - Left: Stock Movement, Right: Sales & Variance
            // Stock Movement Header
            $sheet->setCellValue('A' . $row, 'Stock Movement (Liters)');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
            $row++;
            
            // Stock Movement Data
            $movementData = [
                ['Opening Stock:', number_format($calc['opening_stock'], 2)],
                ['Closing Stock:', number_format($calc['closing_stock'], 2)],
                ['Physical Usage:', number_format($calc['physical_usage'], 2)],
            ];
            
            if ($calc['oil_purchased'] > 0) {
                $movementData[] = ['Oil Purchased:', '+' . number_format($calc['oil_purchased'], 2)];
            }
            
            $movementData[] = ['Adjusted Usage:', number_format($calc['adjusted_physical_usage'], 2)];
            
            foreach ($movementData as $data) {
                $sheet->setCellValue('A' . $row, $data[0]);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValue('B' . $row, $data[1]);
                if ($data[0] == 'Adjusted Usage:') {
                    $sheet->getStyle('B' . $row)->getFont()->setBold(true);
                }
                $row++;
            }
            
            // Move to right column for Sales & Variance
            $tempRow = $row - count($movementData);
            
            // Sales & Variance Header
            $sheet->setCellValue('H' . $tempRow, 'Sales & Variance Analysis');
            $sheet->mergeCells('H' . $tempRow . ':M' . $tempRow);
            $sheet->getStyle('H' . $tempRow)->getFont()->setBold(true);
            $sheet->getStyle('H' . $tempRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
            $tempRow++;
            
            // Sales & Variance Data
            $salesData = [
                ['Nozzle Sales (Litres):', number_format($calc['nozzle_sales_liters'], 2) . ' L'],
                ['Reset Sales (Litres):', number_format($calc['reset_sales_liters'], 2) . ' L'],
                ['Total Nozzle Sales:', number_format($calc['total_nozzle_sales'], 2) . ' L'],
                ['Variance Analysis:', number_format($calc['variance'], 2) . ' L (' . number_format($calc['variance_percent'], 2) . '%)'],
            ];
            
            foreach ($salesData as $data) {
                $sheet->setCellValue('H' . $tempRow, $data[0]);
                $sheet->getStyle('H' . $tempRow)->getFont()->setBold(true);
                $sheet->setCellValue('I' . $tempRow, $data[1]);
                
                if ($data[0] == 'Variance Analysis:') {
                    if ($calc['variance'] > 0) {
                        $sheet->getStyle('I' . $tempRow)->getFont()->getColor()->setARGB('FF28a745');
                        $sheet->getStyle('I' . $tempRow)->getFont()->setBold(true);
                    } elseif ($calc['variance'] < 0) {
                        $sheet->getStyle('I' . $tempRow)->getFont()->getColor()->setARGB('FFdc3545');
                        $sheet->getStyle('I' . $tempRow)->getFont()->setBold(true);
                    }
                }
                
                if ($data[0] == 'Total Nozzle Sales:') {
                    $sheet->getStyle('I' . $tempRow)->getFont()->setBold(true);
                }
                $tempRow++;
            }
            
            $row = max($row, $tempRow) + 1;
            
            // Nozzle Transactions Table
            $tankNozzles = $nozzleReadings->where('nozzle.tank_id', $tankId);
            if (count($tankNozzles) > 0) {
                $sheet->setCellValue('A' . $row, 'Nozzle Transactions');
                $sheet->mergeCells('A' . $row . ':M' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;
                
                // Nozzle Headers
                $nozzleHeaders = ['Nozzle', 'Dispenser', 'Opening', 'Closing', 'Dispensed', 'Rate (Rs.)', 'Amount (Rs.)'];
                foreach ($nozzleHeaders as $col => $header) {
                    $cell = chr(65 + $col) . $row;
                    $sheet->setCellValue($cell, $header);
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
                    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $row++;
                
                foreach ($tankNozzles as $reading) {
                    $sheet->setCellValue('A' . $row, $reading->nozzle->name ?? 'N/A');
                    $sheet->setCellValue('B' . $row, $reading->nozzle->dispenser->name ?? 'N/A');
                    $sheet->setCellValue('C' . $row, number_format($reading->opening_reading, 2));
                    $sheet->setCellValue('D' . $row, number_format($reading->closing_reading, 2));
                    $sheet->setCellValue('E' . $row, number_format($reading->total_dispensed, 2));
                    $sheet->setCellValue('F' . $row, number_format($reading->rate, 2));
                    $sheet->setCellValue('G' . $row, number_format($reading->total_amount, 2));
                    
                    for ($col = 0; $col <= 6; $col++) {
                        $cell = chr(65 + $col) . $row;
                        $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    }
                    $row++;
                }
            }
            $row += 2;
        }
        
        // ========================================
        // COMPLETE FINANCIAL SUMMARY
        // ========================================
        $sheet->setCellValue('A' . $row, 'Complete Financial Summary');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');
        $sheet->getStyle('A' . $row)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $row++;
        
        // Financial Summary - 3 column layout
        $financialCards = [
            ['Total Revenue', 'Rs. ' . number_format($financialSummary['total_revenue'] ?? 0, 2), '28a745'],
            ['Total Expenses', 'Rs. ' . number_format($financialSummary['total_expenses'] ?? 0, 2), 'dc3545'],
            ['Net Income', 'Rs. ' . number_format($financialSummary['net_income'] ?? 0, 2), ($financialSummary['net_income'] ?? 0) > 0 ? '28a745' : 'dc3545']
        ];
        
        foreach ($financialCards as $index => $card) {
            $col = 65 + ($index * 4); // A, E, I
            $cell = chr($col) . $row;
            $valueCell = chr($col + 1) . $row;
            
            $sheet->setCellValue($cell, $card[0]);
            $sheet->getStyle($cell)->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $sheet->setCellValue($valueCell, $card[1]);
            $sheet->getStyle($valueCell)->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle($valueCell)->getFont()->getColor()->setARGB('FF' . $card[2]);
            $sheet->getStyle($valueCell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $sheet->mergeCells($cell . ':' . $valueCell);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        $row++;
        
        // Sub-text for revenue
        $sheet->setCellValue('A' . $row, 'Fuel: Rs. ' . number_format($financialSummary['fuel_sales'] ?? 0, 2));
        $sheet->getStyle('A' . $row)->getFont()->setSize(8);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A' . $row . ':B' . $row);
        
        $sheet->setCellValue('E' . $row, 'Oil Purchase: Rs. ' . number_format($financialSummary['oil_purchase'] ?? 0, 2));
        $sheet->getStyle('E' . $row)->getFont()->setSize(8);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('E' . $row . ':F' . $row);
        
        $sheet->setCellValue('I' . $row, 'Fuel Card: Rs. ' . number_format($cashFlow->fuelcard ?? 0, 2));
        $sheet->getStyle('I' . $row)->getFont()->setSize(8);
        $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('I' . $row . ':J' . $row);
        $row += 2;
        
        // Cash position cards
        $cashCards = [
            ['Opening Balance', 'Rs. ' . number_format($financialSummary['cash_handover'] ?? 0, 2)],
            ['Closing Balance', 'Rs. ' . number_format($financialSummary['cash_in_hand'] ?? 0, 2)],
            ['Cash in Bank', 'Rs. ' . number_format($financialSummary['cash_in_bank'] ?? 0, 2)],
            ['Total Cash Balance', 'Rs. ' . number_format($financialSummary['total_cash_balance'] ?? 0, 2)]
        ];
        
        foreach ($cashCards as $index => $card) {
            $col = 65 + ($index * 3); // A, D, G, J
            $cell = chr($col) . $row;
            $valueCell = chr($col + 1) . $row;
            
            $sheet->setCellValue($cell, $card[0]);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
            
            $sheet->setCellValue($valueCell, $card[1]);
            $sheet->getStyle($valueCell)->getFont()->setBold(true);
            $sheet->getStyle($valueCell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $sheet->mergeCells($cell . ':' . $valueCell);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        $row += 2;
        
        // ========================================
        // FOOTER
        // ========================================
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->setCellValue('A' . $row, 'Generated by Pump360 • ' . now()->format('M d, Y \a\t H:i'));
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFont()->setSize(8);
        
        // Auto-size all columns
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'Shift_Stock_Report_' . $shift->id . '_' . date('Ymd_His') . '.xlsx';
        // ========================================
// COMPLETE FINANCIAL SUMMARY (FIXED LAYOUT)
// ========================================
$sheet->setCellValue('A' . $row, 'Complete Financial Summary');
$sheet->mergeCells('A' . $row . ':P' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
$sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1a3a5c');
$sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row++;

// First Row - Three Main Cards (Revenue, Expenses, Net Income)
// Revenue Card
$sheet->setCellValue('A' . $row, 'Total Revenue');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, 'Rs. ' . number_format($financialSummary['total_revenue'] ?? 0, 2));
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(14)->setBold(true);
$sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FF28a745');
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, 'Fuel Sales: Rs. ' . number_format($financialSummary['fuel_sales'] ?? 0, 2));
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(9);
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, 'Lube Sales: Rs. ' . number_format($financialSummary['lube_sales'] ?? 0, 2));
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(9);
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, 'Other Income: Rs. ' . number_format($financialSummary['other_income'] ?? 0, 2));
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(9);
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

// Expenses Card
$expenseStartRow = $row - 5;
$sheet->setCellValue('E' . $expenseStartRow, 'Total Expenses');
$sheet->mergeCells('E' . $expenseStartRow . ':H' . $expenseStartRow);
$sheet->getStyle('E' . $expenseStartRow)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('E' . $expenseStartRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . $expenseStartRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('E' . $expenseStartRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->setCellValue('E' . ($expenseStartRow + 1), 'Rs. ' . number_format($financialSummary['total_expenses'] ?? 0, 2));
$sheet->mergeCells('E' . ($expenseStartRow + 1) . ':H' . ($expenseStartRow + 1));
$sheet->getStyle('E' . ($expenseStartRow + 1))->getFont()->setSize(14)->setBold(true);
$sheet->getStyle('E' . ($expenseStartRow + 1))->getFont()->getColor()->setARGB('FFdc3545');
$sheet->getStyle('E' . ($expenseStartRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . ($expenseStartRow + 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->setCellValue('E' . ($expenseStartRow + 2), 'Oil Purchase: Rs. ' . number_format($financialSummary['oil_purchase'] ?? 0, 2));
$sheet->mergeCells('E' . ($expenseStartRow + 2) . ':H' . ($expenseStartRow + 2));
$sheet->getStyle('E' . ($expenseStartRow + 2))->getFont()->setSize(9);
$sheet->getStyle('E' . ($expenseStartRow + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . ($expenseStartRow + 2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->setCellValue('E' . ($expenseStartRow + 3), 'Lube Purchase: Rs. ' . number_format($financialSummary['lube_purchase'] ?? 0, 2));
$sheet->mergeCells('E' . ($expenseStartRow + 3) . ':H' . ($expenseStartRow + 3));
$sheet->getStyle('E' . ($expenseStartRow + 3))->getFont()->setSize(9);
$sheet->getStyle('E' . ($expenseStartRow + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . ($expenseStartRow + 3))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->setCellValue('E' . ($expenseStartRow + 4), 'Other Expenses: Rs. ' . number_format($financialSummary['other_expenses'] ?? 0, 2));
$sheet->mergeCells('E' . ($expenseStartRow + 4) . ':H' . ($expenseStartRow + 4));
$sheet->getStyle('E' . ($expenseStartRow + 4))->getFont()->setSize(9);
$sheet->getStyle('E' . ($expenseStartRow + 4))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . ($expenseStartRow + 4))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Net Income Card
$netStartRow = $row - 5;
$sheet->setCellValue('I' . $netStartRow, 'Net Income');
$sheet->mergeCells('I' . $netStartRow . ':L' . $netStartRow);
$sheet->getStyle('I' . $netStartRow)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('I' . $netStartRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('I' . $netStartRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('I' . $netStartRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->setCellValue('I' . ($netStartRow + 1), 'Rs. ' . number_format($financialSummary['net_income'] ?? 0, 2));
$sheet->mergeCells('I' . ($netStartRow + 1) . ':L' . ($netStartRow + 1));
$sheet->getStyle('I' . ($netStartRow + 1))->getFont()->setSize(14)->setBold(true);
if (($financialSummary['net_income'] ?? 0) > 0) {
    $sheet->getStyle('I' . ($netStartRow + 1))->getFont()->getColor()->setARGB('FF28a745');
} else {
    $sheet->getStyle('I' . ($netStartRow + 1))->getFont()->getColor()->setARGB('FFdc3545');
}
$sheet->getStyle('I' . ($netStartRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('I' . ($netStartRow + 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Blank space in Net Income card
$sheet->setCellValue('I' . ($netStartRow + 2), '');
$sheet->mergeCells('I' . ($netStartRow + 2) . ':L' . ($netStartRow + 2));
$sheet->getStyle('I' . ($netStartRow + 2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->setCellValue('I' . ($netStartRow + 3), '');
$sheet->mergeCells('I' . ($netStartRow + 3) . ':L' . ($netStartRow + 3));
$sheet->getStyle('I' . ($netStartRow + 3))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->setCellValue('I' . ($netStartRow + 4), '');
$sheet->mergeCells('I' . ($netStartRow + 4) . ':L' . ($netStartRow + 4));
$sheet->getStyle('I' . ($netStartRow + 4))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$row += 2;

// Second Row - Cash Position Cards
$sheet->setCellValue('A' . $row, 'Opening Balance');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, 'Rs. ' . number_format($financialSummary['cash_handover'] ?? 0, 2));
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
$sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FF17a2b8');
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, '');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, '');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, '');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row = $row - 4;

// Closing Balance Card
$sheet->setCellValue('E' . $row, 'Closing Balance');
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('E' . $row, 'Rs. ' . number_format($financialSummary['cash_in_hand'] ?? 0, 2));
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getFont()->setSize(12)->setBold(true);
$sheet->getStyle('E' . $row)->getFont()->getColor()->setARGB('FF17a2b8');
$sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('E' . $row, '');
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('E' . $row, '');
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('E' . $row, '');
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row = $row - 4;

// Cash in Bank Card
$sheet->setCellValue('I' . $row, 'Cash in Bank');
$sheet->mergeCells('I' . $row . ':L' . $row);
$sheet->getStyle('I' . $row)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('I' . $row, 'Rs. ' . number_format($financialSummary['cash_in_bank'] ?? 0, 2));
$sheet->mergeCells('I' . $row . ':L' . $row);
$sheet->getStyle('I' . $row)->getFont()->setSize(12)->setBold(true);
$sheet->getStyle('I' . $row)->getFont()->getColor()->setARGB('FF17a2b8');
$sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('I' . $row, '');
$sheet->mergeCells('I' . $row . ':L' . $row);
$sheet->getStyle('I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('I' . $row, '');
$sheet->mergeCells('I' . $row . ':L' . $row);
$sheet->getStyle('I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('I' . $row, '');
$sheet->mergeCells('I' . $row . ':L' . $row);
$sheet->getStyle('I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row = $row - 4;

// Total Cash Balance Card
$sheet->setCellValue('M' . $row, 'Total Cash Balance');
$sheet->mergeCells('M' . $row . ':P' . $row);
$sheet->getStyle('M' . $row)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('M' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('M' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('M' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('M' . $row, 'Rs. ' . number_format($financialSummary['total_cash_balance'] ?? 0, 2));
$sheet->mergeCells('M' . $row . ':P' . $row);
$sheet->getStyle('M' . $row)->getFont()->setSize(12)->setBold(true);
$sheet->getStyle('M' . $row)->getFont()->getColor()->setARGB('FF28a745');
$sheet->getStyle('M' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('M' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('M' . $row, '');
$sheet->mergeCells('M' . $row . ':P' . $row);
$sheet->getStyle('M' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('M' . $row, '');
$sheet->mergeCells('M' . $row . ':P' . $row);
$sheet->getStyle('M' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('M' . $row, '');
$sheet->mergeCells('M' . $row . ':P' . $row);
$sheet->getStyle('M' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row += 2;

// Third Row - Additional Cards (Credit Card, etc.)
$sheet->setCellValue('A' . $row, 'Credit Card');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, 'Rs. ' . number_format($cashFlow->credit_card ?? 0, 2));
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
$sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FF17a2b8');
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, '');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('A' . $row, '');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row = $row - 3;

// Fuel Card
$sheet->setCellValue('E' . $row, 'Fuel Card');
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getFont()->setSize(10)->setBold(true);
$sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('E' . $row, 'Rs. ' . number_format($cashFlow->fuelcard ?? 0, 2));
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getFont()->setSize(12)->setBold(true);
$sheet->getStyle('E' . $row)->getFont()->getColor()->setARGB('FF17a2b8');
$sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('E' . $row, '');
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row++;

$sheet->setCellValue('E' . $row, '');
$sheet->mergeCells('E' . $row . ':H' . $row);
$sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$row = $row - 3;

$row += 4;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }



}