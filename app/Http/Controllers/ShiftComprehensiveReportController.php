<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftComprehensiveReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;
        $role = strtolower($user->role ?? '');

        $sql = "
            SELECT 
                s.id,
                s.station_id,
                s.shift_no,
                s.shift_incharger,
                s.start_time,
                s.end_time,
                s.cash_handover,
                s.cash_return,
                s.status,
                st.name as station_name,
                u.full_name as incharge_name,
                CASE WHEN s.shift_no = 1 THEN 'Day' ELSE 'Night' END as shift_type
            FROM shifts s
            LEFT JOIN stations st ON s.station_id = st.id
            LEFT JOIN employees e ON s.shift_incharger = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE s.status = 'closed'
        ";

        if ($role === 'employee') {
            $employee = DB::table('employees')->where('user_id', $userId)->first();
            if ($employee) {
                $sql .= " AND s.station_id = " . intval($employee->station_id);
            } else {
                $sql .= " AND 1 = 0";
            }
        } elseif ($role === 'owner') {
            $stationIds = DB::table('stations')->where('user_id', $userId)->pluck('id')->toArray();
            if (!empty($stationIds)) {
                $sql .= " AND s.station_id IN (" . implode(',', $stationIds) . ")";
            } else {
                $sql .= " AND 1 = 0";
            }
        }

        $sql .= " ORDER BY s.id DESC";

        $shifts = DB::select($sql);
        $shifts = collect($shifts);

        return view('comprehensive', compact('shifts'));
    }

    public function show($shiftId)
    {
        try {
            // Shift details
            $shift = DB::selectOne("
                SELECT 
                    s.id,
                    s.station_id,
                    s.shift_no,
                    s.shift_incharger,
                    s.start_time,
                    s.end_time,
                    s.cash_handover,
                    s.cash_return,
                    s.status,
                    st.name as station_name,
                    u.full_name as incharge_name,
                    CASE WHEN s.shift_no = 1 THEN 'Day' ELSE 'Night' END as shift_type
                FROM shifts s
                LEFT JOIN stations st ON s.station_id = st.id
                LEFT JOIN employees e ON s.shift_incharger = e.id
                LEFT JOIN users u ON e.user_id = u.id
                WHERE s.id = ?
            ", [$shiftId]);

            if (!$shift) {
                return response()->json(['error' => 'Shift not found'], 404);
            }

            // Cash Flow
            $cashFlow = DB::selectOne("SELECT * FROM shift_cash_flow WHERE shift_id = ?", [$shiftId]);

            // Tank Values
            $shiftStart = Carbon::parse($shift->start_time);
            $shiftEnd = Carbon::parse($shift->end_time ?? now());

            $tanks = DB::select("
                SELECT 
                    t.id as tank_id,
                    t.name as tank_name,
                    t.product_id,
                    p.name as product_name,
                    sp.id as station_product_id
                FROM tanks t
                LEFT JOIN products p ON t.product_id = p.id
                LEFT JOIN station_products sp ON sp.station_id = t.station_id AND sp.product_id = t.product_id
                WHERE t.station_id = ? AND t.status = 'active'
            ", [$shift->station_id]);

            $tankValues = [];

            foreach ($tanks as $tank) {
                // Get tank dip
                $tankDip = DB::selectOne("
                    SELECT dip_in_liters FROM tanks_dip 
                    WHERE tank_id = ? AND shift_id = ?
                    ORDER BY id DESC LIMIT 1
                ", [$tank->tank_id, $shiftId]);

                $currentLevel = $tankDip ? $tankDip->dip_in_liters : 0;

                // Get product price using station_product_id
                $price = 0;
                if ($tank->station_product_id) {
                    $productPrice = DB::selectOne("
                        SELECT price FROM product_prices 
                        WHERE station_product_id = ?
                        AND effective_from <= ?
                        AND (effective_to >= ? OR effective_to IS NULL)
                        ORDER BY effective_from DESC LIMIT 1
                    ", [$tank->station_product_id, $shiftStart, $shiftEnd]);

                    $price = $productPrice ? $productPrice->price : 0;
                }

                // Fallback: latest price
                if ($price == 0 && $tank->station_product_id) {
                    $latestPrice = DB::selectOne("
                        SELECT price FROM product_prices 
                        WHERE station_product_id = ?
                        ORDER BY effective_from DESC LIMIT 1
                    ", [$tank->station_product_id]);

                    $price = $latestPrice ? $latestPrice->price : 0;
                }

                $value = $currentLevel * $price;

                $tankValues[] = [
                    'tank_name' => $tank->tank_name,
                    'product_name' => $tank->product_name ?? 'Unknown',
                    'current_level' => $currentLevel,
                    'price' => $price,
                    'value' => $value,
                ];
            }

            // Lube Values
            $lubeInventory = DB::select("
                SELECT 
                    li.product_id,
                    p.name as product_name,
                    li.quantity,
                    li.avg_buying_price
                FROM lube_inventory li
                LEFT JOIN products p ON li.product_id = p.id
                WHERE li.station_id = ? AND li.quantity > 0
            ", [$shift->station_id]);

            $lubeValues = [];
            foreach ($lubeInventory as $item) {
                $lubeValues[] = [
                    'product_name' => $item->product_name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'avg_price' => $item->avg_buying_price ?? 0,
                    'value' => $item->quantity * ($item->avg_buying_price ?? 0),
                ];
            }

            // Total Purchases
            $oilPurchases = DB::selectOne("
                SELECT SUM(ort.recived_qty * op.rate) as total
                FROM oil_recived_tanks ort
                JOIN oil_purchase op ON op.id = ort.oil_purchase_id
                WHERE ort.shift_id = ?
            ", [$shiftId]);

            $lubePurchases = DB::selectOne("
                SELECT SUM(ll.line_amount) as total
                FROM lube_documents ld
                JOIN lube_lines ll ON ll.document_id = ld.id
                WHERE ld.shift_id = ? 
                AND ld.doc_type = 'purchase' 
                AND ld.payment_status = 'paid'
            ", [$shiftId]);

            $totalOilPurchases = $oilPurchases ? $oilPurchases->total : 0;
            $totalLubePurchases = $lubePurchases ? $lubePurchases->total : 0;
            $totalPurchases = $totalOilPurchases + $totalLubePurchases;

            // Summary
            $totalTankValue = collect($tankValues)->sum('value');
            $totalLubeValue = collect($lubeValues)->sum('value');
            $totalInventoryValue = $totalTankValue + $totalLubeValue;

            $cashInHand = $cashFlow ? $cashFlow->in_hand : 0;
            $cashInBank = $cashFlow ? $cashFlow->in_bank : 0;
            $fuelCard = $cashFlow ? $cashFlow->fuelcard : 0;
            $creditCard = $cashFlow ? $cashFlow->creditcard : 0;
            $cashHandover = $shift->cash_handover ?? 0;
            $totalCash = $cashInHand + $cashInBank + $fuelCard + $creditCard;

            $totalInvestment = $cashHandover + $totalPurchases;
            $profitLoss = $totalInventoryValue - $totalInvestment;
            $profitLossPercentage = $totalInvestment > 0 ? ($profitLoss / $totalInvestment) * 100 : 0;

            $summary = [
                'cash_handover' => $cashHandover,
                'cash_in_hand' => $cashInHand,
                'cash_in_bank' => $cashInBank,
                'fuel_card' => $fuelCard,
                'credit_card' => $creditCard,
                'total_cash' => $totalCash,
                'total_tank_value' => $totalTankValue,
                'total_lube_value' => $totalLubeValue,
                'total_inventory_value' => $totalInventoryValue,
                'total_purchases' => $totalPurchases,
                'oil_purchases' => $totalOilPurchases,
                'lube_purchases' => $totalLubePurchases,
                'total_investment' => $totalInvestment,
                'profit_loss' => $profitLoss,
                'profit_loss_percentage' => $profitLossPercentage,
            ];

            return response()->json([
                'shift' => [
                    'id' => $shift->id,
                    'station_name' => $shift->station_name ?? 'N/A',
                    'shift_no' => $shift->shift_no,
                    'incharge' => $shift->incharge_name ?? 'N/A',
                    'status' => $shift->status,
                    'start_time' => Carbon::parse($shift->start_time)->format('M d, Y H:i'),
                    'end_time' => $shift->end_time ? Carbon::parse($shift->end_time)->format('M d, Y H:i') : 'Not Ended',
                ],
                'summary' => $summary,
                'tankValues' => $tankValues,
                'lubeValues' => $lubeValues,
            ]);

        } catch (\Exception $e) {
            \Log::error('Comprehensive Report Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ✅ HELPER FUNCTIONS
    private function getTankValuesSQL($shiftId)
    {
        $tankValues = [];

        $shift = DB::selectOne("SELECT station_id, start_time, end_time FROM shifts WHERE id = ?", [$shiftId]);
        if (!$shift)
            return $tankValues;

        $shiftStart = Carbon::parse($shift->start_time);
        $shiftEnd = Carbon::parse($shift->end_time ?? now());

        $tanks = DB::select("
            SELECT 
                t.id as tank_id,
                t.name as tank_name,
                t.product_id,
                p.name as product_name,
                sp.id as station_product_id
            FROM tanks t
            LEFT JOIN products p ON t.product_id = p.id
            LEFT JOIN station_products sp ON sp.station_id = t.station_id AND sp.product_id = t.product_id
            WHERE t.station_id = ? AND t.status = 'active'
        ", [$shift->station_id]);

        foreach ($tanks as $tank) {
            $tankDip = DB::selectOne("
                SELECT dip_in_liters FROM tanks_dip 
                WHERE tank_id = ? AND shift_id = ?
                ORDER BY id DESC LIMIT 1
            ", [$tank->tank_id, $shiftId]);

            $currentLevel = $tankDip ? $tankDip->dip_in_liters : 0;

            $price = 0;
            if ($tank->station_product_id) {
                $productPrice = DB::selectOne("
                    SELECT price FROM product_prices 
                    WHERE station_product_id = ?
                    AND effective_from <= ?
                    AND (effective_to >= ? OR effective_to IS NULL)
                    ORDER BY effective_from DESC LIMIT 1
                ", [$tank->station_product_id, $shiftStart, $shiftEnd]);

                $price = $productPrice ? $productPrice->price : 0;
            }

            // Fallback
            if ($price == 0 && $tank->station_product_id) {
                $latestPrice = DB::selectOne("
                    SELECT price FROM product_prices 
                    WHERE station_product_id = ?
                    ORDER BY effective_from DESC LIMIT 1
                ", [$tank->station_product_id]);

                $price = $latestPrice ? $latestPrice->price : 0;
            }

            $value = $currentLevel * $price;

            $tankValues[] = [
                'tank_name' => $tank->tank_name,
                'product_name' => $tank->product_name ?? 'Unknown',
                'current_level' => $currentLevel,
                'price' => $price,
                'value' => $value,
            ];
        }

        return $tankValues;
    }

    private function getLubeValuesSQL($stationId)
    {
        $lubeValues = [];

        $lubeInventory = DB::select("
            SELECT 
                li.product_id,
                p.name as product_name,
                li.quantity,
                li.avg_buying_price
            FROM lube_inventory li
            LEFT JOIN products p ON li.product_id = p.id
            WHERE li.station_id = ? AND li.quantity > 0
        ", [$stationId]);

        foreach ($lubeInventory as $item) {
            $lubeValues[] = [
                'product_name' => $item->product_name ?? 'Unknown',
                'quantity' => $item->quantity,
                'avg_price' => $item->avg_buying_price ?? 0,
                'value' => $item->quantity * ($item->avg_buying_price ?? 0),
            ];
        }

        return $lubeValues;
    }

    private function getTotalPurchasesSQL($shiftId)
    {
        $oilPurchases = DB::selectOne("
            SELECT SUM(ort.recived_qty * op.rate) as total
            FROM oil_recived_tanks ort
            JOIN oil_purchase op ON op.id = ort.oil_purchase_id
            WHERE ort.shift_id = ?
        ", [$shiftId]);

        $lubePurchases = DB::selectOne("
            SELECT SUM(ll.line_amount) as total
            FROM lube_documents ld
            JOIN lube_lines ll ON ll.document_id = ld.id
            WHERE ld.shift_id = ? 
            AND ld.doc_type = 'purchase' 
            AND ld.payment_status = 'paid'
        ", [$shiftId]);

        return [
            'oil' => $oilPurchases ? $oilPurchases->total : 0,
            'lube' => $lubePurchases ? $lubePurchases->total : 0,
            'total' => ($oilPurchases ? $oilPurchases->total : 0) + ($lubePurchases ? $lubePurchases->total : 0),
        ];
    }

    // private function getSummarySQL($shift, $tankValues, $lubeValues, $totalPurchases)
    // {
    //     $cashFlow = DB::selectOne("SELECT * FROM shift_cash_flow WHERE shift_id = ?", [$shift->id]);

    //     $totalTankValue = collect($tankValues)->sum('value');
    //     $totalLubeValue = collect($lubeValues)->sum('value');
    //     $totalInventoryValue = $totalTankValue + $totalLubeValue;

    //     $cashInHand = $cashFlow ? $cashFlow->in_hand : 0;
    //     $cashInBank = $cashFlow ? $cashFlow->in_bank : 0;
    //     $fuelCard = $cashFlow ? $cashFlow->fuelcard : 0;
    //     $creditCard = $cashFlow ? $cashFlow->creditcard : 0;
    //     $cashHandover = $shift->cash_handover ?? 0;
    //     $totalCash = $cashInHand + $cashInBank + $fuelCard + $creditCard;

    //     $totalInvestment = $cashHandover + $totalPurchases['total'];
    //     $profitLoss = $totalInventoryValue - $totalInvestment;
    //     $profitLossPercentage = $totalInvestment > 0 ? ($profitLoss / $totalInvestment) * 100 : 0;

    //     return [
    //         'cash_handover' => $cashHandover,
    //         'cash_in_hand' => $cashInHand,
    //         'cash_in_bank' => $cashInBank,
    //         'fuel_card' => $fuelCard,
    //         'credit_card' => $creditCard,
    //         'total_cash' => $totalCash,
    //         'total_tank_value' => $totalTankValue,
    //         'total_lube_value' => $totalLubeValue,
    //         'total_inventory_value' => $totalInventoryValue,
    //         'total_purchases' => $totalPurchases['total'],
    //         'oil_purchases' => $totalPurchases['oil'],
    //         'lube_purchases' => $totalPurchases['lube'],
    //         'total_investment' => $totalInvestment,
    //         'profit_loss' => $profitLoss,
    //         'profit_loss_percentage' => $profitLossPercentage,
    //     ];
    // }

    private function getSummarySQL($shift, $tankValues, $lubeValues, $totalPurchases)
{
    $cashFlow = DB::selectOne("SELECT * FROM shift_cash_flow WHERE shift_id = ?", [$shift->id]);

    $totalTankValue = collect($tankValues)->sum('value');
    $totalLubeValue = collect($lubeValues)->sum('value');
    $totalInventoryValue = $totalTankValue + $totalLubeValue;

    // ✅ TOTAL CASH from shift_cash_flow
    $cashInHand = $cashFlow ? $cashFlow->in_hand : 0;
    $cashInBank = $cashFlow ? $cashFlow->in_bank : 0;
    $fuelCard = $cashFlow ? $cashFlow->fuelcard : 0;
    $creditCard = $cashFlow ? $cashFlow->creditcard : 0;
    
    // ✅ TOTAL CASH = in_hand + in_bank + fuelcard + creditcard
    $totalCash = $cashInHand + $cashInBank + $fuelCard + $creditCard;

    // ✅ CASH HANDOVER (Opening)
    $cashHandover = $shift->cash_handover ?? 0;

    // ✅ PROFIT/LOSS = Inventory Value - Total Cash
    $profitLoss = $totalInventoryValue - $totalCash;

    // ✅ PROFIT/LOSS PERCENTAGE (based on investment/cash handover)
    $profitLossPercentage = 0;
    if ($cashHandover > 0) {
        $profitLossPercentage = ($profitLoss / $cashHandover) * 100;
    }

    // ✅ Total Purchases (for display only)
    $totalPurchasesAmount = $totalPurchases['total'] ?? 0;

    return [
        'cash_handover' => (float) $cashHandover,
        'cash_in_hand' => (float) $cashInHand,
        'cash_in_bank' => (float) $cashInBank,
        'fuel_card' => (float) $fuelCard,
        'credit_card' => (float) $creditCard,
        'total_cash' => (float) $totalCash,
        'total_tank_value' => (float) $totalTankValue,
        'total_lube_value' => (float) $totalLubeValue,
        'total_inventory_value' => (float) $totalInventoryValue,
        'total_purchases' => (float) $totalPurchasesAmount,
        'oil_purchases' => (float) ($totalPurchases['oil'] ?? 0),
        'lube_purchases' => (float) ($totalPurchases['lube'] ?? 0),
        'total_investment' => (float) $cashHandover,
        'profit_loss' => (float) $profitLoss,
        'profit_loss_percentage' => (float) $profitLossPercentage,
    ];
}

    

    // ✅ SINGLE exportExcel FUNCTION
    public function exportExcel($shiftId)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $shift = DB::selectOne("
        SELECT 
            s.id,
            s.station_id,
            s.shift_no,
            s.start_time,
            s.end_time,
            s.cash_handover,
            s.cash_return,
            s.status,
            st.name as station_name,
            u.full_name as incharge_name
        FROM shifts s
        LEFT JOIN stations st ON s.station_id = st.id
        LEFT JOIN employees e ON s.shift_incharger = e.id
        LEFT JOIN users u ON e.user_id = u.id
        WHERE s.id = ?
    ", [$shiftId]);

        if (!$shift) {
            return response()->json(['error' => 'Shift not found'], 404);
        }

        $tankValues = $this->getTankValuesSQL($shiftId);
        $lubeValues = $this->getLubeValuesSQL($shift->station_id);
        $totalPurchases = $this->getTotalPurchasesSQL($shiftId);
        $summary = $this->getSummarySQL($shift, $tankValues, $lubeValues, $totalPurchases);

        // Create Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Shift Report');

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        $row = 1;

        // HEADER
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'Comprehensive Shift Report');
        $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
        $row++;

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', $shift->station_name ?? 'Fuel Station');
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);
        $row++;

        $sheet->mergeCells('A3:N3');
        $sheet->setCellValue('A3', 'Professional Fuel Management System');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $row += 2;

        // SHIFT DETAILS
        $sheet->setCellValue('A' . $row, 'Shift Details');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $sheet->setCellValue('A' . $row, 'Shift ID:');
        $sheet->setCellValue('B' . $row, $shift->id);
        $sheet->setCellValue('D' . $row, 'Shift Type:');
        $sheet->setCellValue('E' . $row, $shift->shift_no == 1 ? 'Day Shift' : 'Night Shift');
        $sheet->setCellValue('G' . $row, 'Status:');
        $sheet->setCellValue('H' . $row, ucfirst($shift->status));
        $row++;

        $sheet->setCellValue('A' . $row, 'Start Time:');
        $sheet->setCellValue('B' . $row, Carbon::parse($shift->start_time)->format('M d, Y H:i'));
        $sheet->setCellValue('D' . $row, 'End Time:');
        $sheet->setCellValue('E' . $row, $shift->end_time ? Carbon::parse($shift->end_time)->format('M d, Y H:i') : 'Not Ended');
        $sheet->setCellValue('G' . $row, 'Incharge:');
        $sheet->setCellValue('H' . $row, $shift->incharge_name ?? 'N/A');
        $row += 2;

        $sheet->getStyle('A' . ($row - 4) . ':I' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // CASH RECONCILIATION
        $sheet->setCellValue('A' . $row, 'Cash Reconciliation');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $cashData = [
            ['Cash Handover (Opening Balance)', (float) ($summary['cash_handover'] ?? 0)],
            ['Cash in Hand', (float) ($summary['cash_in_hand'] ?? 0)],
            ['Cash in Bank', (float) ($summary['cash_in_bank'] ?? 0)],
            ['Fuel Card', (float) ($summary['fuel_card'] ?? 0)],
            ['Credit Card', (float) ($summary['credit_card'] ?? 0)],
            ['Total Cash Balance', (float) ($summary['total_cash'] ?? 0)],
        ];

        foreach ($cashData as $data) {
            $sheet->setCellValue('A' . $row, $data[0]);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $row, 'Rs. ' . number_format($data[1], 2));
            if ($data[0] == 'Total Cash Balance') {
                $sheet->getStyle('B' . $row)->getFont()->setBold(true);
                $sheet->getStyle('B' . $row)->getFont()->getColor()->setARGB('FF28a745');
            }
            $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row++;
        }
        $row++;

        // TANK VALUES
        $sheet->setCellValue('A' . $row, 'Products Value in Tanks');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $headers = ['#', 'Tank Name', 'Product', 'Current Level (L)', 'Price (Rs.)', 'Value (Rs.)'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF34495e');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }
        $row++;

        $counter = 1;
        foreach ($tankValues as $tank) {
            $sheet->setCellValue('A' . $row, $counter);
            $sheet->setCellValue('B' . $row, $tank['tank_name']);
            $sheet->setCellValue('C' . $row, $tank['product_name']);
            $sheet->setCellValue('D' . $row, number_format((float) $tank['current_level'], 2));
            $sheet->setCellValue('E' . $row, number_format((float) $tank['price'], 2));
            $sheet->setCellValue('F' . $row, 'Rs. ' . number_format((float) $tank['value'], 2));

            for ($col = 0; $col <= 5; $col++) {
                $sheet->getStyle(chr(65 + $col) . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
            $row++;
            $counter++;
        }

        if (count($tankValues) > 0) {
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
            $sheet->setCellValue('F' . $row, 'Rs. ' . number_format((float) ($summary['total_tank_value'] ?? 0), 2));
            $sheet->getStyle('F' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row += 2;
        }

        // LUBE VALUES
        if (count($lubeValues) > 0) {
            $sheet->setCellValue('A' . $row, 'Products in Store (Lubricants)');
            $sheet->mergeCells('A' . $row . ':N' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $row++;

            $headers = ['#', 'Product Name', 'Quantity', 'Avg Price (Rs.)', 'Value (Rs.)'];
            foreach ($headers as $col => $header) {
                $cell = chr(65 + $col) . $row;
                $sheet->setCellValue($cell, $header);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF34495e');
                $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
            $row++;

            $counter = 1;
            foreach ($lubeValues as $lube) {
                $sheet->setCellValue('A' . $row, $counter);
                $sheet->setCellValue('B' . $row, $lube['product_name']);
                $sheet->setCellValue('C' . $row, number_format((float) $lube['quantity'], 2));
                $sheet->setCellValue('D' . $row, number_format((float) $lube['avg_price'], 2));
                $sheet->setCellValue('E' . $row, 'Rs. ' . number_format((float) $lube['value'], 2));

                for ($col = 0; $col <= 4; $col++) {
                    $sheet->getStyle(chr(65 + $col) . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
                $row++;
                $counter++;
            }

            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8E8E8');
            $sheet->setCellValue('E' . $row, 'Rs. ' . number_format((float) ($summary['total_lube_value'] ?? 0), 2));
            $sheet->getStyle('E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row += 2;
        }

        // PROFIT / LOSS
        $sheet->setCellValue('A' . $row, 'Profit / Loss Summary');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF2c3e50');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $summaryData = [
            ['Opening Cash Handover', (float) ($summary['cash_handover'] ?? 0)],
            ['Oil Purchases During Shift', (float) ($summary['oil_purchases'] ?? 0)],
            ['Lube Purchases During Shift', (float) ($summary['lube_purchases'] ?? 0)],
            ['Total Purchases', (float) ($summary['total_purchases'] ?? 0)],
            ['Total Investment (Opening Balance + Purchases)', (float) ($summary['total_investment'] ?? 0)],
            ['Total Inventory Value (Tanks + Store)', (float) ($summary['total_inventory_value'] ?? 0)],
            ['PROFIT / LOSS', (float) ($summary['profit_loss'] ?? 0)],
            ['Profit/Loss Percentage', (float) ($summary['profit_loss_percentage'] ?? 0)],
        ];

        foreach ($summaryData as $data) {
            $sheet->setCellValue('A' . $row, $data[0]);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);

            if (strpos($data[0], 'PROFIT / LOSS') !== false) {
                $sheet->setCellValue('B' . $row, 'Rs. ' . number_format($data[1], 2));
                if ($data[1] >= 0) {
                    $sheet->getStyle('B' . $row)->getFont()->getColor()->setARGB('FF28a745');
                } else {
                    $sheet->getStyle('B' . $row)->getFont()->getColor()->setARGB('FFdc3545');
                }
                $sheet->getStyle('B' . $row)->getFont()->setBold(true);
            } elseif (strpos($data[0], 'Percentage') !== false) {
                $sheet->setCellValue('B' . $row, number_format($data[1], 2) . '%');
                if ($data[1] >= 0) {
                    $sheet->getStyle('B' . $row)->getFont()->getColor()->setARGB('FF28a745');
                } else {
                    $sheet->getStyle('B' . $row)->getFont()->getColor()->setARGB('FFdc3545');
                }
                $sheet->getStyle('B' . $row)->getFont()->setBold(true);
            } else {
                $sheet->setCellValue('B' . $row, 'Rs. ' . number_format($data[1], 2));
            }

            $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row++;
        }

        // FOOTER
        $row += 2;
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->setCellValue('A' . $row, 'Generated by Pump360 • ' . now()->format('M d, Y \a\t H:i'));
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFont()->setSize(8);

        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);

        $sheet->getStyle('A1:F' . $row)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Comprehensive_Shift_Report_' . $shift->id . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

}