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
                'tank_dip_image' => $shiftDip ? $shiftDip->tanks_dip_image : null, // ✅ NEW

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

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Shift Stock Report');

        // Set page layout
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(15);

        // Center alignment for all cells
        $sheet->getStyle('A1:Z500')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:Z500')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $row = 1;

        // ========================================
        // HEADER SECTION
        // ========================================
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', 'Shift Stock Reconciliation Report');
        $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
        $row++;

        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A2', $shift->station->name ?? 'Fuel Station');
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);
        $row++;

        $sheet->mergeCells('A3:M3');
        $sheet->setCellValue('A3', 'Professional Fuel Management System');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $row++;

        $sheet->setCellValue('A4', 'Report ID: SHIFT-' . $shift->id);
        $sheet->mergeCells('A4:D4');
        $sheet->getStyle('A4')->getFont()->setSize(9);
        $sheet->setCellValue('J4', 'Generated: ' . now()->format('M d, Y H:i'));
        $sheet->mergeCells('J4:M4');
        $sheet->getStyle('J4')->getFont()->setSize(9);
        $row += 2;

        // ========================================
        // SHIFT OVERVIEW
        // ========================================
        $sheet->setCellValue('A' . $row, 'Shift Overview');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $sheet->setCellValue('A' . $row, 'Station');
        $sheet->setCellValue('B' . $row, $shift->station->name ?? 'N/A');
        $sheet->setCellValue('D' . $row, 'Shift Type');
        $sheet->setCellValue('E' . $row, $shift->shift_no == 1 ? 'Day Shift' : 'Night Shift');
        $sheet->setCellValue('G' . $row, 'Shift Incharge');
        $sheet->setCellValue('H' . $row, $shift->shiftIncharger->user->full_name ?? 'N/A');
        $row++;

        $sheet->setCellValue('A' . $row, 'Opening Balance');
        $sheet->setCellValue('B' . $row, 'Rs. ' . number_format($shift->cash_handover ?? 0, 2));
        $sheet->setCellValue('D' . $row, 'Status');
        $sheet->setCellValue('E' . $row, ucfirst($shift->status ?? 'Closed'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Start Time');
        $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($shift->start_time)->format('M d, Y H:i'));
        $sheet->setCellValue('D' . $row, 'End Time');
        $sheet->setCellValue('E' . $row, $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('M d, Y H:i') : 'Not Ended');
        $row += 2;

        // Apply borders to shift overview
        $sheet->getStyle('A' . ($row - 5) . ':H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A' . ($row - 5) . ':A' . ($row - 1))->getFont()->setBold(true);
        $sheet->getStyle('D' . ($row - 5) . ':D' . ($row - 1))->getFont()->setBold(true);
        $sheet->getStyle('G' . ($row - 5) . ':G' . ($row - 1))->getFont()->setBold(true);

        // ========================================
// FORMULA INFO BOX (NEW ADDITION)
// ========================================
        $sheet->setCellValue('A' . $row, '📊 Formula Guide - Stock Calculation');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6F7FF');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FF0066CC');
        $row++;

        $sheet->setCellValue('A' . $row, '• Physical Usage = Opening Stock - Closing Stock (Fuel sold during shift)');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(9);
        $row++;

        $sheet->setCellValue('A' . $row, '• Adjusted Usage = Physical Usage + Oil Received (Actual stock consumed)');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(9);
        $row++;

        $sheet->setCellValue('A' . $row, '• Total Nozzle Sales = Nozzle Sales + Reset Sales (Total fuel dispensed)');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(9);
        $row++;

        $sheet->setCellValue('A' . $row, '• Variance (Gain/Loss) = Total Nozzle Sales - Adjusted Usage');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(9);
        $row++;

        $sheet->setCellValue('A' . $row, '• GAIN = Positive Variance (More sales recorded than actual stock)');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(9);
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FF28a745');
        $row++;

        $sheet->setCellValue('A' . $row, '• LOSS = Negative Variance (Less sales recorded than actual stock)');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(9);
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFdc3545');
        $row++;

        $sheet->setCellValue('A' . $row, '• BALANCED = Zero Variance (Exact match between sales and stock)');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(9);
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FF17a2b8');
        $row += 2;

        // Apply border to info box
        $sheet->getStyle('A' . ($row - 8) . ':M' . ($row - 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('A' . ($row - 8) . ':M' . ($row - 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F9FF');

        // ========================================
        // PROFESSIONAL STOCK ANALYSIS
        // ========================================
        $sheet->setCellValue('A' . $row, 'Professional Stock Analysis');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $headers = ['Tank', 'Product', 'Opening (L)', 'Closing (L)', 'Oil Received (L)', 'Nozzle Sales (L)', 'Variance (L)', 'Variance %', 'Status'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF34495e');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        $row++;

        foreach ($tankCalculations as $calc) {
            $sheet->setCellValue('A' . $row, $calc['tank_name']);
            $sheet->setCellValue('B' . $row, $calc['product_name']);
            $sheet->setCellValue('C' . $row, number_format($calc['opening_stock'], 2));
            $sheet->setCellValue('D' . $row, number_format($calc['closing_stock'], 2));
            $sheet->setCellValue('E' . $row, number_format($calc['oil_purchased'], 2));
            $sheet->setCellValue('F' . $row, number_format($calc['total_nozzle_sales'], 2));

            // Variance with +/-
            $varianceValue = ($calc['variance'] > 0 ? '+' : '') . number_format($calc['variance'], 2);
            $sheet->setCellValue('G' . $row, $varianceValue);

            $sheet->setCellValue('H' . $row, $calc['total_nozzle_sales'] > 0 ? number_format($calc['variance_percent'], 2) . '%' : 'N/A');
            $sheet->setCellValue('I' . $row, $calc['status']);

            // Styling
            if ($calc['variance'] > 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setARGB('FF28a745');
            } elseif ($calc['variance'] < 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setARGB('FFdc3545');
            }

            if ($calc['status'] == 'Normal') {
                $sheet->getStyle('I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFd4edda');
            } elseif ($calc['status'] == 'Critical') {
                $sheet->getStyle('I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFf8d7da');
            }

            for ($col = 0; $col <= 8; $col++) {
                $sheet->getStyle(chr(65 + $col) . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        foreach ($tankCalculations as $tankId => $calc) {
            // Tank Title
            $sheet->setCellValue('A' . $row, $calc['tank_name'] . ' - ' . $calc['product_name']);
            $sheet->mergeCells('A' . $row . ':M' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setSize(11)->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1a3a5c');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $row++;

            // ========== LEFT SECTION: Stock Movement ==========
            $sheet->setCellValue('A' . $row, 'Stock Movement (Liters)');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
            $row++;

            $stockStartRow = $row;

            // Opening Stock (direct value)
            $sheet->setCellValue('A' . $row, 'Opening Stock:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $row, number_format($calc['opening_stock'], 2));
            $row++;

            // Closing Stock (direct value)
            $sheet->setCellValue('A' . $row, 'Closing Stock:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $row, number_format($calc['closing_stock'], 2));
            $row++;

            // Physical Usage (direct value from controller)
            $physicalValue = ($calc['physical_usage'] > 0 ? '+' : '') . number_format($calc['physical_usage'], 2);
            $sheet->setCellValue('A' . $row, 'Physical Usage:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $row, $physicalValue);
            $row++;

            // Oil Purchased (if exists)
            if ($calc['oil_purchased'] > 0) {
                $sheet->setCellValue('A' . $row, 'Oil Purchased:');
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->setCellValue('B' . $row, '+' . number_format($calc['oil_purchased'], 2));
                $row++;
            }

            // Adjusted Usage (direct value from controller)
            $adjustedValue = ($calc['adjusted_physical_usage'] > 0 ? '+' : '') . number_format($calc['adjusted_physical_usage'], 2);
            $sheet->setCellValue('A' . $row, 'Adjusted Usage:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $row, $adjustedValue);
            $sheet->getStyle('B' . $row)->getFont()->setBold(true);
            $row++;

            // ========== RIGHT SECTION: Sales & Variance ==========
            $tempRow = $stockStartRow;
            $sheet->setCellValue('H' . $tempRow, 'Sales & Variance Analysis');
            $sheet->mergeCells('H' . $tempRow . ':M' . $tempRow);
            $sheet->getStyle('H' . $tempRow)->getFont()->setBold(true);
            $sheet->getStyle('H' . $tempRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
            $tempRow++;

            // Nozzle Sales
            $sheet->setCellValue('H' . $tempRow, 'Nozzle Sales (Litres):');
            $sheet->getStyle('H' . $tempRow)->getFont()->setBold(true);
            $sheet->setCellValue('I' . $tempRow, number_format($calc['nozzle_sales_liters'], 2));
            $tempRow++;

            // Reset Sales
            $sheet->setCellValue('H' . $tempRow, 'Reset Sales (Litres):');
            $sheet->getStyle('H' . $tempRow)->getFont()->setBold(true);
            $sheet->setCellValue('I' . $tempRow, number_format($calc['reset_sales_liters'], 2));
            $tempRow++;

            // Total Nozzle Sales (direct value)
            $sheet->setCellValue('H' . $tempRow, 'Total Nozzle Sales:');
            $sheet->getStyle('H' . $tempRow)->getFont()->setBold(true);
            $sheet->setCellValue('I' . $tempRow, number_format($calc['total_nozzle_sales'], 2));
            $sheet->getStyle('I' . $tempRow)->getFont()->setBold(true);
            $tempRow++;

            // Variance Analysis (direct value)
            $varianceDisplay = ($calc['variance'] > 0 ? '+' : '') . number_format($calc['variance'], 2) . ' L';
            if ($calc['total_nozzle_sales'] > 0) {
                $varianceDisplay .= ' (' . number_format($calc['variance_percent'], 2) . '%)';
            }
            $sheet->setCellValue('H' . $tempRow, 'Variance Analysis:');
            $sheet->getStyle('H' . $tempRow)->getFont()->setBold(true);
            $sheet->setCellValue('I' . $tempRow, $varianceDisplay);
            if ($calc['variance'] > 0) {
                $sheet->getStyle('I' . $tempRow)->getFont()->getColor()->setARGB('FF28a745');
            } elseif ($calc['variance'] < 0) {
                $sheet->getStyle('I' . $tempRow)->getFont()->getColor()->setARGB('FFdc3545');
            }
            $sheet->getStyle('I' . $tempRow)->getFont()->setBold(true);
            $tempRow++;

            // Apply borders
            $sheet->getStyle('A' . $stockStartRow . ':F' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('H' . $stockStartRow . ':M' . ($tempRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $row = max($row, $tempRow) + 1;

            // ========== Nozzle Transactions Table ==========
            $tankNozzles = $nozzleReadings->filter(function ($reading) use ($tankId) {
                return $reading->nozzle && $reading->nozzle->tank_id == $tankId;
            });

            if (count($tankNozzles) > 0) {
                $sheet->setCellValue('A' . $row, 'Nozzle Transactions');
                $sheet->mergeCells('A' . $row . ':M' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;

                $nozzleHeaders = ['Nozzle', 'Dispenser', 'Opening', 'Closing', 'Dispensed', 'Rate (Rs.)', 'Amount (Rs.)'];
                foreach ($nozzleHeaders as $col => $header) {
                    $cell = chr(65 + $col) . $row;
                    $sheet->setCellValue($cell, $header);
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF34495e');
                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
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
                        $sheet->getStyle(chr(65 + $col) . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        // 3 Column Layout
        $finStartRow = $row;

        $sheet->setCellValue('A' . $finStartRow, 'Total Revenue');
        $sheet->setCellValue('B' . $finStartRow, 'Rs. ' . number_format($financialSummary['total_revenue'] ?? 0, 2));
        $sheet->setCellValue('D' . $finStartRow, 'Total Expenses');
        $sheet->setCellValue('E' . $finStartRow, 'Rs. ' . number_format($financialSummary['total_expenses'] ?? 0, 2));
        $sheet->setCellValue('G' . $finStartRow, 'Net Income');
        $sheet->setCellValue('H' . $finStartRow, 'Rs. ' . number_format($financialSummary['net_income'] ?? 0, 2));
        $finStartRow++;

        $sheet->setCellValue('A' . $finStartRow, 'Fuel Sales:');
        $sheet->setCellValue('B' . $finStartRow, 'Rs. ' . number_format($financialSummary['fuel_sales'] ?? 0, 2));
        $sheet->setCellValue('D' . $finStartRow, 'Oil Purchase:');
        $sheet->setCellValue('E' . $finStartRow, 'Rs. ' . number_format($financialSummary['oil_purchase'] ?? 0, 2));
        $sheet->setCellValue('G' . $finStartRow, 'Fuel Card:');
        $sheet->setCellValue('H' . $finStartRow, 'Rs. ' . number_format($cashFlow->fuelcard ?? 0, 2));
        $finStartRow++;

        $sheet->setCellValue('A' . $finStartRow, 'Lube Sales:');
        $sheet->setCellValue('B' . $finStartRow, 'Rs. ' . number_format($financialSummary['lube_sales'] ?? 0, 2));
        $sheet->setCellValue('D' . $finStartRow, 'Lube Purchase:');
        $sheet->setCellValue('E' . $finStartRow, 'Rs. ' . number_format($financialSummary['lube_purchase'] ?? 0, 2));
        $sheet->setCellValue('G' . $finStartRow, 'Credit Card:');
        $sheet->setCellValue('H' . $finStartRow, 'Rs. ' . number_format($cashFlow->creditcard ?? 0, 2));
        $finStartRow++;

        $sheet->setCellValue('A' . $finStartRow, 'Other Income:');
        $sheet->setCellValue('B' . $finStartRow, 'Rs. ' . number_format($financialSummary['transaction_income'] ?? 0, 2));
        $sheet->setCellValue('D' . $finStartRow, 'Other Expenses:');
        $sheet->setCellValue('E' . $finStartRow, 'Rs. ' . number_format($financialSummary['transaction_expense'] ?? 0, 2));
        $finStartRow++;

        // Apply borders
        $sheet->getStyle('A' . ($row) . ':H' . ($finStartRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A' . ($row) . ':A' . ($finStartRow - 1))->getFont()->setBold(true);
        $sheet->getStyle('D' . ($row) . ':D' . ($finStartRow - 1))->getFont()->setBold(true);
        $sheet->getStyle('G' . ($row) . ':G' . ($finStartRow - 1))->getFont()->setBold(true);

        $row = $finStartRow + 1;

        // ========================================
        // CASH POSITION
        // ========================================
        $cashRow = $row;

        // Headings
        $sheet->setCellValue('A' . $cashRow, 'Opening Balance');
        $sheet->mergeCells('A' . $cashRow . ':C' . $cashRow);
        $sheet->getStyle('A' . $cashRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $cashRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');

        $sheet->setCellValue('D' . $cashRow, 'Closing Balance');
        $sheet->mergeCells('D' . $cashRow . ':F' . $cashRow);
        $sheet->getStyle('D' . $cashRow)->getFont()->setBold(true);
        $sheet->getStyle('D' . $cashRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');

        $sheet->setCellValue('G' . $cashRow, 'Cash in Bank');
        $sheet->mergeCells('G' . $cashRow . ':I' . $cashRow);
        $sheet->getStyle('G' . $cashRow)->getFont()->setBold(true);
        $sheet->getStyle('G' . $cashRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');

        $sheet->setCellValue('J' . $cashRow, 'Total Cash Balance');
        $sheet->mergeCells('J' . $cashRow . ':M' . $cashRow);
        $sheet->getStyle('J' . $cashRow)->getFont()->setBold(true);
        $sheet->getStyle('J' . $cashRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
        $cashRow++;

        // Values
        $sheet->setCellValue('A' . $cashRow, 'Rs. ' . number_format($financialSummary['cash_handover'] ?? 0, 2));
        $sheet->mergeCells('A' . $cashRow . ':C' . $cashRow);
        $sheet->getStyle('A' . $cashRow)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $cashRow)->getFont()->getColor()->setARGB('FF17a2b8');

        $sheet->setCellValue('D' . $cashRow, 'Rs. ' . number_format($financialSummary['cash_in_hand'] ?? 0, 2));
        $sheet->mergeCells('D' . $cashRow . ':F' . $cashRow);
        $sheet->getStyle('D' . $cashRow)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('D' . $cashRow)->getFont()->getColor()->setARGB('FF17a2b8');

        $sheet->setCellValue('G' . $cashRow, 'Rs. ' . number_format($financialSummary['cash_in_bank'] ?? 0, 2));
        $sheet->mergeCells('G' . $cashRow . ':I' . $cashRow);
        $sheet->getStyle('G' . $cashRow)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('G' . $cashRow)->getFont()->getColor()->setARGB('FF17a2b8');

        $sheet->setCellValue('J' . $cashRow, 'Rs. ' . number_format($financialSummary['total_cash_balance'] ?? 0, 2));
        $sheet->mergeCells('J' . $cashRow . ':M' . $cashRow);
        $sheet->getStyle('J' . $cashRow)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('J' . $cashRow)->getFont()->getColor()->setARGB('FF28a745');
        $cashRow++;

        // Apply borders
        $sheet->getStyle('A' . ($cashRow - 2) . ':M' . ($cashRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row = $cashRow + 1;

        // ========================================
        // OTHER FINANCIAL TRANSACTIONS
        // ========================================
        $transactionsSummary = $this->calculateTransactionsSummary($transactions);

        $sheet->setCellValue('A' . $row, 'Other Financial Transactions');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        // Income Summary
        $sheet->setCellValue('A' . $row, 'Income Summary');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
        $row++;

        $incomeData = [
            ['Total Income:', 'Rs. ' . number_format($transactionsSummary['income']['total'] ?? 0, 2)],
            ['Cash Received:', 'Rs. ' . number_format($transactionsSummary['income']['cash'] ?? 0, 2)],
            ['Bank Received:', 'Rs. ' . number_format($transactionsSummary['income']['bank'] ?? 0, 2)],
            ['Card Received:', 'Rs. ' . number_format($transactionsSummary['income']['card'] ?? 0, 2)],
            ['On Credit:', 'Rs. ' . number_format($transactionsSummary['income']['credit'] ?? 0, 2)],
        ];

        foreach ($incomeData as $data) {
            $sheet->setCellValue('A' . $row, $data[0]);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $row, $data[1]);
            $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // Expense Summary
        $expenseRowStart = $row - count($incomeData);
        $sheet->setCellValue('H' . $expenseRowStart, 'Expense Summary');
        $sheet->mergeCells('H' . $expenseRowStart . ':M' . $expenseRowStart);
        $sheet->getStyle('H' . $expenseRowStart)->getFont()->setBold(true);
        $sheet->getStyle('H' . $expenseRowStart)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
        $expenseRowStart++;

        $expenseData = [
            ['Total Expense:', 'Rs. ' . number_format($transactionsSummary['expense']['total'] ?? 0, 2)],
            ['Cash Paid:', 'Rs. ' . number_format($transactionsSummary['expense']['cash'] ?? 0, 2)],
            ['Bank Paid:', 'Rs. ' . number_format($transactionsSummary['expense']['bank'] ?? 0, 2)],
            ['Card Paid:', 'Rs. ' . number_format($transactionsSummary['expense']['card'] ?? 0, 2)],
            ['On Credit:', 'Rs. ' . number_format($transactionsSummary['expense']['credit'] ?? 0, 2)],
        ];

        foreach ($expenseData as $data) {
            $sheet->setCellValue('H' . $expenseRowStart, $data[0]);
            $sheet->getStyle('H' . $expenseRowStart)->getFont()->setBold(true);
            $sheet->setCellValue('I' . $expenseRowStart, $data[1]);
            if ($data[0] == 'Total Expense:') {
                $sheet->getStyle('I' . $expenseRowStart)->getFont()->getColor()->setARGB('FFdc3545');
                $sheet->getStyle('I' . $expenseRowStart)->getFont()->setBold(true);
            }
            $sheet->getStyle('H' . $expenseRowStart . ':I' . $expenseRowStart)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $expenseRowStart++;
        }

        $row = max($row, $expenseRowStart) + 2;

        // Transaction Details Table
        if ($transactions->count() > 0) {
            $sheet->setCellValue('A' . $row, 'Transaction Details');
            $sheet->mergeCells('A' . $row . ':M' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            $detailHeaders = ['Type', 'Account', 'To Account', 'Payment Method', 'Debit', 'Credit', 'Note', 'Time'];
            foreach ($detailHeaders as $col => $header) {
                $cell = chr(65 + $col) . $row;
                $sheet->setCellValue($cell, $header);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF34495e');
                $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
            $row++;

            foreach ($transactions as $transaction) {
                $sheet->setCellValue('A' . $row, ucfirst($transaction->type));
                $sheet->setCellValue('B' . $row, $transaction->account->name ?? 'N/A');
                $sheet->setCellValue('C' . $row, $transaction->toAccount->name ?? 'N/A');
                $sheet->setCellValue('D' . $row, ucfirst($transaction->method));
                $sheet->setCellValue('E' . $row, $transaction->type == 'expense' ? 'Rs. ' . number_format($transaction->debit, 2) : '-');
                $sheet->setCellValue('F' . $row, $transaction->type == 'income' ? 'Rs. ' . number_format($transaction->credit, 2) : '-');
                $sheet->setCellValue('G' . $row, $transaction->note);
                $sheet->setCellValue('H' . $row, \Carbon\Carbon::parse($transaction->created_at)->format('H:i'));

                for ($col = 0; $col <= 7; $col++) {
                    $sheet->getStyle(chr(65 + $col) . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $row++;
            }
            $row++;

            // Net Transactions
            $netTransactions = ($transactionsSummary['income']['total'] ?? 0) - ($transactionsSummary['expense']['total'] ?? 0);
            $sheet->setCellValue('A' . $row, 'Net Transactions:');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('E' . $row, 'Rs. ' . number_format($transactionsSummary['expense']['total'] ?? 0, 2));
            $sheet->setCellValue('F' . $row, 'Rs. ' . number_format($transactionsSummary['income']['total'] ?? 0, 2));
            $sheet->setCellValue('G' . $row, 'Net: Rs. ' . number_format($netTransactions, 2));
            $sheet->mergeCells('G' . $row . ':H' . $row);

            if ($netTransactions > 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setARGB('FF28a745');
            } else {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setARGB('FFdc3545');
            }
            $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // ========================================
        // FOOTER
        // ========================================
        $row += 2;
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->setCellValue('A' . $row, 'Generated by Pump360 • ' . now()->format('M d, Y \a\t H:i'));
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFont()->setSize(8);
        $lastRow = $row;

        // Apply main outer border
        $sheet->getStyle('A1:M' . $lastRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'Shift_Stock_Report_' . $shift->id . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }



}