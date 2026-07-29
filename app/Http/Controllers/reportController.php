<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Dynamic System Settings
     */
    protected string $unit;
    protected int $defaultDays;
    protected int $recentTxLimit;
    protected int $purchaseLimit;
    protected int $fifoDisplayLimit;
    protected array $chartColors;

    public function __construct()
    {
        // Purely Volume-based dynamic settings (No monetary currency)
        $this->unit             = config('reports.unit', 'L');
        $this->defaultDays      = (int) config('reports.default_days', 30);
        $this->recentTxLimit    = (int) config('reports.recent_tx_limit', 25);
        $this->purchaseLimit    = (int) config('reports.purchase_limit', 50);
        $this->fifoDisplayLimit = (int) config('reports.fifo_display_limit', 20);
        $this->chartColors      = config('reports.chart_colors', ['#1a237e', '#0091ea', '#00c853', '#ff6d00', '#6a1b9a', '#d50000']);
    }

    /**
     * Pure Volume Gain/Loss Dashboard
     */
    public function index(Request $request)
    {
        // Dynamic Filters
        $stationId = $request->input('station_id');
        $tankId    = $request->input('tank_id');
        
        $fromDate = $request->input('from_date', Carbon::now()->subDays($this->defaultDays)->format('Y-m-d'));
        $toDate   = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        // Dynamic Dropdowns
        $stations = DB::select("SELECT id, name FROM stations ORDER BY name ASC");
        $tanks    = DB::select("SELECT id, name, station_id FROM tanks ORDER BY name ASC");

        // ---------- Volume Data Processing ----------
        $stats           = $this->getOverviewStats($stationId, $tankId, $fromDate, $toDate);
        $tankPerformance = $this->getTankVolumeGainLoss($stationId, $tankId, $fromDate, $toDate);
        $fifoLayers      = $this->getFifoLayers($stationId, $tankId);
        $purchases       = $this->getPurchaseHistory($stationId, $tankId, $fromDate, $toDate, $this->purchaseLimit);
        $transactions    = $this->getRecentTransactions($stationId, $tankId, $fromDate, $toDate, $this->recentTxLimit);
        $salesTrend      = $this->getVolumeTrend($stationId, $tankId, $fromDate, $toDate);
        $productSplit    = $this->getProductWiseVolume($stationId, $tankId, $fromDate, $toDate);

        // System Settings Array
        $settings = [
            'unit'              => $this->unit,
            'recentTxLimit'     => $this->recentTxLimit,
            'fifoDisplayLimit'  => $this->fifoDisplayLimit,
            'chartColors'       => $this->chartColors,
        ];

        return view('report', compact(
            'stations',
            'tanks',
            'stats',
            'tankPerformance',
            'fifoLayers',
            'purchases',
            'transactions',
            'salesTrend',
            'productSplit',
            'stationId',
            'tankId',
            'fromDate',
            'toDate',
            'settings'
        ));
    }

    /**
     * AJAX - Fetch Unlimited Transactions (Volume Only)
     */
    public function fetchUnlimitedData(Request $request)
    {
        $stationId = $request->input('station_id');
        $tankId    = $request->input('tank_id');
        $fromDate  = $request->input('from_date', Carbon::now()->subDays($this->defaultDays)->format('Y-m-d'));
        $toDate    = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        $rows = $this->getRecentTransactions($stationId, $tankId, $fromDate, $toDate, null);

        $formatted = array_map(function ($row) {
            return [
                'id'      => $row->id,
                'date'    => Carbon::parse($row->transaction_date)->format('Y-m-d H:i'),
                'station' => $row->station_name ?? 'N/A',
                'tank'    => $row->tank_name ?? 'N/A',
                'nozzle'  => $row->nozzle_name ?? 'N/A',
                'product' => $row->product_name ?? 'N/A',
                'qty'     => number_format($row->qty, 2),
            ];
        }, $rows);

        return response()->json([
            'status' => 'success',
            'data'   => $formatted,
        ]);
    }

    /* ==========================================================
     *                     PRIVATE HELPERS
     * ========================================================== */

    private function getOverviewStats($stationId, $tankId, $fromDate, $toDate)
    {
        // --- Tanks Count ---
        $tankSql = "SELECT COUNT(*) as cnt FROM tanks t WHERE 1=1";
        $tankParams = [];
        if ($stationId) { $tankSql .= " AND t.station_id = ?"; $tankParams[] = $stationId; }
        if ($tankId)    { $tankSql .= " AND t.id = ?"; $tankParams[] = $tankId; }
        $totalTanks = DB::select($tankSql, $tankParams)[0]->cnt ?? 0;

        // --- Dispensers Count ---
        $dispenserSql = "SELECT COUNT(DISTINCT td.dispenser_id) as cnt
                          FROM tank_dispenser td
                          JOIN tanks t ON td.tank_id = t.id WHERE 1=1";
        $dispenserParams = [];
        if ($stationId) { $dispenserSql .= " AND t.station_id = ?"; $dispenserParams[] = $stationId; }
        if ($tankId)    { $dispenserSql .= " AND t.id = ?"; $dispenserParams[] = $tankId; }
        $totalDispensers = DB::select($dispenserSql, $dispenserParams)[0]->cnt ?? 0;

        // --- Nozzles Count ---
        $nozzleSql = "SELECT COUNT(n.id) as cnt FROM nozzles n JOIN tanks t ON n.tank_id = t.id WHERE 1=1";
        $nozzleParams = [];
        if ($stationId) { $nozzleSql .= " AND t.station_id = ?"; $nozzleParams[] = $stationId; }
        if ($tankId)    { $nozzleSql .= " AND t.id = ?"; $nozzleParams[] = $tankId; }
        $totalNozzles = DB::select($nozzleSql, $nozzleParams)[0]->cnt ?? 0;

        // --- Volume Dispensed (Sales Qty) ---
        $salesSql = "SELECT
                        COUNT(snr.id) as total_transactions,
                        COALESCE(SUM(snr.total_dispensed), 0) as total_dispensed_qty
                     FROM shift_nozzle_readings snr
                     JOIN nozzles n ON snr.nozzle_id = n.id
                     JOIN tanks t ON n.tank_id = t.id
                     WHERE DATE(snr.created_at) BETWEEN ? AND ?";
        $salesParams = [$fromDate, $toDate];
        if ($stationId) { $salesSql .= " AND t.station_id = ?"; $salesParams[] = $stationId; }
        if ($tankId)    { $salesSql .= " AND t.id = ?"; $salesParams[] = $tankId; }
        $sales = DB::select($salesSql, $salesParams)[0];

        // --- Volume Received (Purchases Qty) ---
        $purchaseSql = "SELECT
                            COALESCE(SUM(COALESCE(ort.recived_qty, op.recieved_qty, 0)), 0) as total_received_qty,
                            SUM(CASE WHEN LOWER(op.recive_status) LIKE '%not%' THEN 1 ELSE 0 END) as pending_orders
                        FROM oil_purchase op
                        LEFT JOIN (
                            SELECT oil_purchase_id, SUM(recived_qty) as recived_qty
                            FROM oil_recived_tanks GROUP BY oil_purchase_id
                        ) ort ON ort.oil_purchase_id = op.id
                        WHERE op.recieving_date BETWEEN ? AND ?";
        $purchaseParams = [$fromDate, $toDate];
        if ($stationId) { $purchaseSql .= " AND op.station_id = ?"; $purchaseParams[] = $stationId; }
        if ($tankId)    { $purchaseSql .= " AND op.tank_id = ?"; $purchaseParams[] = $tankId; }
        $purchase = DB::select($purchaseSql, $purchaseParams)[0];

        // --- Current Remaining Active FIFO Stock Volume ---
        $currentStockSql = "SELECT COALESCE(SUM(fil.remaining_qty), 0) as total_current_stock
                             FROM fuel_inventory_layers fil
                             JOIN tanks t ON fil.tank_id = t.id
                             WHERE fil.remaining_qty > 0";
        $stockParams = [];
        if ($stationId) { $currentStockSql .= " AND t.station_id = ?"; $stockParams[] = $stationId; }
        if ($tankId)    { $currentStockSql .= " AND t.id = ?"; $stockParams[] = $tankId; }
        $currentStock = DB::select($currentStockSql, $stockParams)[0]->total_current_stock ?? 0;

        return [
            'total_tanks'         => (int) $totalTanks,
            'total_dispensers'    => (int) $totalDispensers,
            'total_nozzles'       => (int) $totalNozzles,
            'total_transactions'  => (int) ($sales->total_transactions ?? 0),
            'total_dispensed_qty' => (float) ($sales->total_dispensed_qty ?? 0),
            'total_received_qty'  => (float) ($purchase->total_received_qty ?? 0),
            'total_current_stock' => (float) $currentStock,
            'pending_orders'      => (int) ($purchase->pending_orders ?? 0),
        ];
    }

    /**
     * Pure Volume Tank-wise Gain/Loss
     * Calculation: (Current Active Stock + Total Volume Dispensed) - Total Volume Received
     */
    private function getTankVolumeGainLoss($stationId, $tankId, $fromDate, $toDate)
    {
        $sql = "SELECT
                    t.id as tank_id,
                    t.name as tank_name,
                    s.name as station_name,
                    p.name as product_name,
                    
                    /* Total Received Qty */
                    COALESCE(recv.total_received, 0) as total_received_qty,
                    
                    /* Total Dispensed Qty */
                    COALESCE(disp.total_dispensed, 0) as total_dispensed_qty,
                    
                    /* Current Remaining Layer Stock */
                    COALESCE(stk.remaining_stock, 0) as current_stock_qty,
                    
                    /* Pure Volume Gain / Loss (In Liters) */
                    (COALESCE(disp.total_dispensed, 0) + COALESCE(stk.remaining_stock, 0)) - COALESCE(recv.total_received, 0) as volume_gain_loss

                FROM tanks t
                JOIN stations s ON t.station_id = s.id
                JOIN products p ON t.product_id = p.id

                /* Subquery for Received Qty in Period */
                LEFT JOIN (
                    SELECT op.tank_id, SUM(COALESCE(ort.recived_qty, op.recieved_qty, 0)) as total_received
                    FROM oil_purchase op
                    LEFT JOIN (
                        SELECT oil_purchase_id, SUM(recived_qty) as recived_qty 
                        FROM oil_recived_tanks GROUP BY oil_purchase_id
                    ) ort ON ort.oil_purchase_id = op.id
                    WHERE op.recieving_date BETWEEN ? AND ?
                    GROUP BY op.tank_id
                ) recv ON recv.tank_id = t.id

                /* Subquery for Dispensed Qty in Period */
                LEFT JOIN (
                    SELECT n.tank_id, SUM(snr.total_dispensed) as total_dispensed
                    FROM shift_nozzle_readings snr
                    JOIN nozzles n ON snr.nozzle_id = n.id
                    WHERE DATE(snr.created_at) BETWEEN ? AND ?
                    GROUP BY n.tank_id
                ) disp ON disp.tank_id = t.id

                /* Subquery for Current Remaining Stock */
                LEFT JOIN (
                    SELECT fil.tank_id, SUM(fil.remaining_qty) as remaining_stock
                    FROM fuel_inventory_layers fil
                    WHERE fil.remaining_qty > 0
                    GROUP BY fil.tank_id
                ) stk ON stk.tank_id = t.id

                WHERE 1=1";
                
        $params = [$fromDate, $toDate, $fromDate, $toDate];

        if ($stationId) {
            $sql .= " AND t.station_id = ?";
            $params[] = $stationId;
        }
        if ($tankId) {
            $sql .= " AND t.id = ?";
            $params[] = $tankId;
        }

        $sql .= " ORDER BY volume_gain_loss DESC";

        return DB::select($sql, $params);
    }

    private function getFifoLayers($stationId, $tankId)
    {
        $sql = "SELECT
                    fil.id,
                    t.id as tank_id,
                    t.name as tank_name,
                    s.name as station_name,
                    p.name as product_name,
                    fil.remaining_qty,
                    fil.created_at
                FROM fuel_inventory_layers fil
                JOIN tanks t ON fil.tank_id = t.id
                JOIN products p ON fil.product_id = p.id
                JOIN stations s ON t.station_id = s.id
                WHERE fil.remaining_qty > 0";
        $params = [];

        if ($stationId) { $sql .= " AND t.station_id = ?"; $params[] = $stationId; }
        if ($tankId)    { $sql .= " AND t.id = ?"; $params[] = $tankId; }

        $sql .= " ORDER BY t.name ASC, fil.created_at ASC";

        return DB::select($sql, $params);
    }

    private function getPurchaseHistory($stationId, $tankId, $fromDate, $toDate, ?int $limit = 50)
    {
        $sql = "SELECT
                    op.id,
                    op.order_date,
                    op.recieving_date,
                    op.recive_status,
                    p.name as product_name,
                    t.name as tank_name,
                    s.name as station_name,
                    op.qty as ordered_qty,
                    COALESCE(ort.recived_qty, 0) as recived_qty
                FROM oil_purchase op
                JOIN products p ON op.product_id = p.id
                LEFT JOIN tanks t ON t.id = op.tank_id
                LEFT JOIN stations s ON s.id = op.station_id
                LEFT JOIN (
                    SELECT oil_purchase_id, SUM(recived_qty) as recived_qty
                    FROM oil_recived_tanks GROUP BY oil_purchase_id
                ) ort ON ort.oil_purchase_id = op.id
                WHERE op.recieving_date BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) { $sql .= " AND op.station_id = ?"; $params[] = $stationId; }
        if ($tankId)    { $sql .= " AND op.tank_id = ?"; $params[] = $tankId; }

        $sql .= " ORDER BY op.recieving_date DESC";
        if ($limit) { $sql .= " LIMIT " . (int) $limit; }

        return DB::select($sql, $params);
    }

    private function getRecentTransactions($stationId, $tankId, $fromDate, $toDate, ?int $limit = 25)
    {
        $sql = "SELECT
                    snr.id,
                    snr.created_at as transaction_date,
                    s.name as station_name,
                    t.name as tank_name,
                    n.name as nozzle_name,
                    p.name as product_name,
                    snr.total_dispensed as qty
                FROM shift_nozzle_readings snr
                JOIN nozzles n ON snr.nozzle_id = n.id
                JOIN tanks t ON n.tank_id = t.id
                JOIN products p ON t.product_id = p.id
                JOIN stations s ON t.station_id = s.id
                WHERE DATE(snr.created_at) BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) { $sql .= " AND t.station_id = ?"; $params[] = $stationId; }
        if ($tankId)    { $sql .= " AND t.id = ?"; $params[] = $tankId; }

        $sql .= " ORDER BY snr.created_at DESC";
        if ($limit) { $sql .= " LIMIT " . (int) $limit; }

        return DB::select($sql, $params);
    }

    private function getVolumeTrend($stationId, $tankId, $fromDate, $toDate)
    {
        $sql = "SELECT
                    DATE(snr.created_at) as sale_date,
                    COALESCE(SUM(snr.total_dispensed), 0) as qty
                FROM shift_nozzle_readings snr
                JOIN nozzles n ON snr.nozzle_id = n.id
                JOIN tanks t ON n.tank_id = t.id
                WHERE DATE(snr.created_at) BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) { $sql .= " AND t.station_id = ?"; $params[] = $stationId; }
        if ($tankId)    { $sql .= " AND t.id = ?"; $params[] = $tankId; }

        $sql .= " GROUP BY DATE(snr.created_at) ORDER BY sale_date ASC";

        return DB::select($sql, $params);
    }

    private function getProductWiseVolume($stationId, $tankId, $fromDate, $toDate)
    {
        $sql = "SELECT
                    p.name as product_name,
                    COALESCE(SUM(snr.total_dispensed), 0) as total_qty
                FROM shift_nozzle_readings snr
                JOIN nozzles n ON snr.nozzle_id = n.id
                JOIN tanks t ON n.tank_id = t.id
                JOIN products p ON t.product_id = p.id
                WHERE DATE(snr.created_at) BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) { $sql .= " AND t.station_id = ?"; $params[] = $stationId; }
        if ($tankId)    { $sql .= " AND t.id = ?"; $params[] = $tankId; }

        $sql .= " GROUP BY p.name ORDER BY total_qty DESC";

        return DB::select($sql, $params);
    }
}