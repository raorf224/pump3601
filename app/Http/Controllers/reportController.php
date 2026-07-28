<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class reportController extends Controller
{
    /**
     * Main Overview Dashboard
     * Tank-wise Gain/Loss, FIFO Layers, Purchases, Recent Sales, Charts
     * All data pulled with plain SQL (DB::select) so it's easy to debug
     * against the same queries you already tested in phpMyAdmin.
     */
    public function index(Request $request)
    {
        // ---------- Filters ----------
        $stationId = $request->input('station_id');
        $tankId = $request->input('tank_id');
        $fromDate = $request->input('from_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        // Dropdown data
        $stations = DB::select("SELECT id, name FROM stations ORDER BY name ASC");
        $tanks = DB::select("SELECT id, name, station_id FROM tanks ORDER BY name ASC");

        // ---------- 1. Overview Stat Cards ----------
        $stats = $this->getOverviewStats($stationId, $tankId, $fromDate, $toDate);

        // ---------- 2. Tank-wise Gain/Loss (FIFO based) ----------
        $tankPerformance = $this->getTankPerformance($stationId, $tankId, $fromDate, $toDate);

        // ---------- 3. FIFO Inventory Layers (current stock left) ----------
        $fifoLayers = $this->getFifoLayers($stationId, $tankId);

        // ---------- 4. Recent Purchases & Receives ----------
        $purchases = $this->getPurchaseHistory($stationId, $tankId, $fromDate, $toDate);

        // ---------- 5. Recent Transactions (latest 25) ----------
        $transactions = $this->getRecentTransactions($stationId, $tankId, $fromDate, $toDate, 25);

        // ---------- 6. Chart Data ----------
        $salesTrend = $this->getSalesTrend($stationId, $tankId, $fromDate, $toDate);
        $productSplit = $this->getProductWiseSales($stationId, $tankId, $fromDate, $toDate);

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
            'toDate'
        ));
    }

    /**
     * AJAX - unlimited transactions for the "Show More" modal
     */
    public function fetchUnlimitedData(Request $request)
    {
        $stationId = $request->input('station_id');
        $tankId = $request->input('tank_id');
        $fromDate = $request->input('from_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        $rows = $this->getRecentTransactions($stationId, $tankId, $fromDate, $toDate, null);

        $formatted = array_map(function ($row) {
            return [
                'id' => $row->id,
                'date' => Carbon::parse($row->transaction_date)->format('Y-m-d H:i'),
                'station' => $row->station_name ?? 'N/A',
                'tank' => $row->tank_name ?? 'N/A',
                'nozzle' => $row->nozzle_name ?? 'N/A',
                'product' => $row->product_name ?? 'N/A',
                'qty' => $row->qty,
                'rate' => number_format($row->sale_rate, 2),
                'amount' => number_format($row->total_amount, 2),
                'profit' => number_format($row->profit, 2),
            ];
        }, $rows);

        return response()->json([
            'status' => 'success',
            'data' => $formatted,
        ]);
    }

    /* ==========================================================
     *                     PRIVATE HELPERS
     * ========================================================== */

    /**
     * Top stat cards: tanks / dispensers / nozzles counts + sales,
     * purchase and profit totals for the selected period.
     *
     * IMPORTANT: nozzles.station_id / dispensers.station_id are not
     * reliably populated in this dataset (that's why Nozzles showed 0
     * even when Dispensers showed a number). So every count here is
     * derived through the real hierarchy instead:
     *   Station -> Tanks -> (tank_dispenser -> Dispensers) & (Nozzles via tank_id)
     */
    private function getOverviewStats($stationId, $tankId, $fromDate, $toDate)
    {
        // --- Tanks (station + tank scoped) ---
        $tankSql = "SELECT COUNT(*) as cnt FROM tanks t WHERE 1=1";
        $tankParams = [];
        if ($stationId) {
            $tankSql .= " AND t.station_id = ?";
            $tankParams[] = $stationId;
        }
        if ($tankId) {
            $tankSql .= " AND t.id = ?";
            $tankParams[] = $tankId;
        }
        $totalTanks = DB::select($tankSql, $tankParams)[0]->cnt;

        // --- Dispensers (via tank_dispenser, scoped to the same tanks) ---
        $dispenserSql = "SELECT COUNT(DISTINCT td.dispenser_id) as cnt
                          FROM tank_dispenser td
                          JOIN tanks t ON td.tank_id = t.id
                          WHERE 1=1";
        $dispenserParams = [];
        if ($stationId) {
            $dispenserSql .= " AND t.station_id = ?";
            $dispenserParams[] = $stationId;
        }
        if ($tankId) {
            $dispenserSql .= " AND t.id = ?";
            $dispenserParams[] = $tankId;
        }
        $totalDispensers = DB::select($dispenserSql, $dispenserParams)[0]->cnt;

        // --- Nozzles (via nozzles.tank_id -> tanks, scoped the same way) ---
        $nozzleSql = "SELECT COUNT(n.id) as cnt
                       FROM nozzles n
                       JOIN tanks t ON n.tank_id = t.id
                       WHERE 1=1";
        $nozzleParams = [];
        if ($stationId) {
            $nozzleSql .= " AND t.station_id = ?";
            $nozzleParams[] = $stationId;
        }
        if ($tankId) {
            $nozzleSql .= " AND t.id = ?";
            $nozzleParams[] = $tankId;
        }
        $totalNozzles = DB::select($nozzleSql, $nozzleParams)[0]->cnt;

        // --- Sales totals (shift_nozzle_readings, joined through the same reliable tank chain) ---
        $salesSql = "SELECT
                        COUNT(snr.id) as total_transactions,
                        COALESCE(SUM(snr.total_dispensed), 0) as total_qty,
                        COALESCE(SUM(snr.total_amount), 0) as total_amount
                     FROM shift_nozzle_readings snr
                     JOIN nozzles n ON snr.nozzle_id = n.id
                     JOIN tanks t ON n.tank_id = t.id
                     WHERE DATE(snr.created_at) BETWEEN ? AND ?";
        $salesParams = [$fromDate, $toDate];

        if ($stationId) {
            $salesSql .= " AND t.station_id = ?";
            $salesParams[] = $stationId;
        }
        if ($tankId) {
            $salesSql .= " AND t.id = ?";
            $salesParams[] = $tankId;
        }

        $sales = DB::select($salesSql, $salesParams)[0];

        // --- Net Gain/Loss (fuel_layer_consumptions -> fuel_inventory_layers -> tanks) ---
        // Same bridge pattern as FuelConsumptionReportController, so this number
        // always matches the FIFO ledger, not the (sometimes stale) sales table.
        $profitSql = "SELECT COALESCE(SUM(fc.profit), 0) as total_profit
                      FROM fuel_layer_consumptions fc
                      JOIN fuel_inventory_layers fil ON fc.layer_id = fil.id
                      JOIN tanks t ON fil.tank_id = t.id
                      WHERE DATE(fc.created_at) BETWEEN ? AND ?";
        $profitParams = [$fromDate, $toDate];

        if ($stationId) {
            $profitSql .= " AND t.station_id = ?";
            $profitParams[] = $stationId;
        }
        if ($tankId) {
            $profitSql .= " AND t.id = ?";
            $profitParams[] = $tankId;
        }

        $totalProfit = DB::select($profitSql, $profitParams)[0]->total_profit;

        // --- Purchase totals ---
        $purchaseSql = "SELECT
                            COUNT(op.id) as total_orders,
                            COALESCE(SUM(op.qty), 0) as total_ordered_qty,
                            COALESCE(SUM(COALESCE(ort.recived_qty, op.recieved_qty, 0) * op.rate), 0) as total_purchase_cost,
                            SUM(CASE WHEN op.recive_status = 'Not-Recived' THEN 1 ELSE 0 END) as pending_orders
                        FROM oil_purchase op
                        LEFT JOIN (
                            SELECT oil_purchase_id, SUM(recived_qty) as recived_qty
                            FROM oil_recived_tanks GROUP BY oil_purchase_id
                        ) ort ON ort.oil_purchase_id = op.id
                        WHERE op.recieving_date BETWEEN ? AND ?";
        $purchaseParams = [$fromDate, $toDate];

        if ($stationId) {
            $purchaseSql .= " AND op.station_id = ?";
            $purchaseParams[] = $stationId;
        }
        if ($tankId) {
            $purchaseSql .= " AND op.tank_id = ?";
            $purchaseParams[] = $tankId;
        }

        $purchase = DB::select($purchaseSql, $purchaseParams)[0];

        return [
            'total_tanks' => (int) $totalTanks,
            'total_dispensers' => (int) $totalDispensers,
            'total_nozzles' => (int) $totalNozzles,
            'total_transactions' => (int) $sales->total_transactions,
            'total_qty' => (float) $sales->total_qty,
            'total_amount' => (float) $sales->total_amount,
            'total_profit' => (float) $totalProfit,
            'total_orders' => (int) $purchase->total_orders,
            'total_ordered_qty' => (float) $purchase->total_ordered_qty,
            'total_purchase_cost' => (float) $purchase->total_purchase_cost,
            'pending_orders' => (int) $purchase->pending_orders,
        ];
    }

    /**
     * Tank-wise Gain/Loss report — built from fuel_layer_consumptions
     * (the real FIFO cost/sale/profit trail) instead of the sales table,
     * so cost_rate always matches the layer the fuel actually came from.
     */
    private function getTankPerformance($stationId, $tankId, $fromDate, $toDate)
    {
        $sql = "SELECT
                    t.id as tank_id,
                    t.name as tank_name,
                    s.name as station_name,
                    p.name as product_name,
                    COUNT(fc.id) as total_sales,
                    COALESCE(SUM(fc.qty), 0) as total_qty_sold,
                    COALESCE(SUM(fc.cost_amount), 0) as total_cost,
                    COALESCE(SUM(fc.sale_amount), 0) as total_revenue,
                    COALESCE(SUM(fc.profit), 0) as total_profit,
                    COALESCE(AVG(fc.cost_rate), 0) as avg_cost_rate,
                    COALESCE(AVG(fc.sale_rate), 0) as avg_sale_rate
                FROM fuel_layer_consumptions fc
                JOIN shift_nozzle_readings snr ON fc.sale_id = snr.id
                JOIN nozzles n ON snr.nozzle_id = n.id
                JOIN tanks t ON n.tank_id = t.id
                JOIN products p ON t.product_id = p.id
                JOIN stations s ON t.station_id = s.id
                WHERE DATE(fc.created_at) BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) {
            $sql .= " AND t.station_id = ?";
            $params[] = $stationId;
        }
        if ($tankId) {
            $sql .= " AND t.id = ?";
            $params[] = $tankId;
        }

        $sql .= " GROUP BY t.id, t.name, s.name, p.name ORDER BY total_profit DESC";

        return DB::select($sql, $params);
    }

    /**
     * FIFO layers currently holding stock (remaining_qty > 0) per tank.
     * No LIMIT here on purpose — this was the bug before (only PMG showed
     * because the old query capped rows before filtering all tanks).
     */
    private function getFifoLayers($stationId, $tankId)
    {
        $sql = "SELECT
                    fil.id,
                    t.id as tank_id,
                    t.name as tank_name,
                    s.name as station_name,
                    p.name as product_name,
                    fil.remaining_qty,
                    fil.rate as cost_rate,
                    fil.created_at
                FROM fuel_inventory_layers fil
                JOIN tanks t ON fil.tank_id = t.id
                JOIN products p ON fil.product_id = p.id
                JOIN stations s ON t.station_id = s.id
                WHERE fil.remaining_qty > 0";
        $params = [];

        if ($stationId) {
            $sql .= " AND t.station_id = ?";
            $params[] = $stationId;
        }
        if ($tankId) {
            $sql .= " AND t.id = ?";
            $params[] = $tankId;
        }

        $sql .= " ORDER BY t.name ASC, fil.created_at ASC";

        return DB::select($sql, $params);
    }

    /**
     * Purchase orders + how much was actually received, at what rate,
     * including partially received orders (not just fully "Recived" ones).
     */
    private function getPurchaseHistory($stationId, $tankId, $fromDate, $toDate)
    {
        $sql = "SELECT
                    op.id,
                    op.order_date,
                    op.recieving_date,
                    op.recive_status,
                    op.payment_status,
                    p.name as product_name,
                    t.name as tank_name,
                    s.name as station_name,
                    op.qty as ordered_qty,
                    COALESCE(ort.recived_qty, 0) as recived_qty,
                    op.rate as purchase_rate,
                    COALESCE(ort.recived_qty * op.rate, 0) as total_cost
                FROM oil_purchase op
                JOIN products p ON op.product_id = p.id
                LEFT JOIN tanks t ON t.id = op.tank_id
                LEFT JOIN stations s ON s.id = op.station_id
                LEFT JOIN (
                    SELECT oil_purchase_id, SUM(recived_qty) as recived_qty
                    FROM oil_recived_tanks
                    GROUP BY oil_purchase_id
                ) ort ON ort.oil_purchase_id = op.id
                WHERE op.recieving_date BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) {
            $sql .= " AND op.station_id = ?";
            $params[] = $stationId;
        }
        if ($tankId) {
            $sql .= " AND op.tank_id = ?";
            $params[] = $tankId;
        }

        $sql .= " ORDER BY op.recieving_date DESC LIMIT 50";

        return DB::select($sql, $params);
    }

    /**
     * Recent sales/transactions — fixed join order (nozzles -> tanks,
     * not the reversed join that was breaking the old query) and
     * filtered by created_at date range.
     */
    private function getRecentTransactions($stationId, $tankId, $fromDate, $toDate, $limit = 25)
    {
        $sql = "SELECT
                    snr.id,
                    snr.created_at as transaction_date,
                    s.name as station_name,
                    t.name as tank_name,
                    n.name as nozzle_name,
                    p.name as product_name,
                    snr.total_dispensed as qty,
                    snr.rate as sale_rate,
                    snr.total_amount,
                    snr.profit
                FROM shift_nozzle_readings snr
                JOIN nozzles n ON snr.nozzle_id = n.id
                JOIN tanks t ON n.tank_id = t.id
                JOIN products p ON t.product_id = p.id
                JOIN stations s ON t.station_id = s.id
                WHERE DATE(snr.created_at) BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) {
            $sql .= " AND t.station_id = ?";
            $params[] = $stationId;
        }
        if ($tankId) {
            $sql .= " AND t.id = ?";
            $params[] = $tankId;
        }

        $sql .= " ORDER BY snr.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }

        return DB::select($sql, $params);
    }

    /**
     * Day-wise sales trend for the line chart.
     */
    private function getSalesTrend($stationId, $tankId, $fromDate, $toDate)
    {
        $sql = "SELECT
                    DATE(snr.created_at) as sale_date,
                    COALESCE(SUM(snr.total_amount), 0) as amount,
                    COALESCE(SUM(snr.total_dispensed), 0) as qty,
                    COALESCE(SUM(snr.profit), 0) as profit
                FROM shift_nozzle_readings snr
                JOIN nozzles n ON snr.nozzle_id = n.id
                JOIN tanks t ON n.tank_id = t.id
                WHERE DATE(snr.created_at) BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) {
            $sql .= " AND t.station_id = ?";
            $params[] = $stationId;
        }
        if ($tankId) {
            $sql .= " AND t.id = ?";
            $params[] = $tankId;
        }

        $sql .= " GROUP BY DATE(snr.created_at) ORDER BY sale_date ASC";

        return DB::select($sql, $params);
    }

    /**
     * Product-wise sales split for the pie/doughnut chart.
     */
    private function getProductWiseSales($stationId, $tankId, $fromDate, $toDate)
    {
        $sql = "SELECT
                    p.name as product_name,
                    COALESCE(SUM(snr.total_dispensed), 0) as total_qty,
                    COALESCE(SUM(snr.total_amount), 0) as total_amount
                FROM shift_nozzle_readings snr
                JOIN nozzles n ON snr.nozzle_id = n.id
                JOIN tanks t ON n.tank_id = t.id
                JOIN products p ON t.product_id = p.id
                WHERE DATE(snr.created_at) BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];

        if ($stationId) {
            $sql .= " AND t.station_id = ?";
            $params[] = $stationId;
        }
        if ($tankId) {
            $sql .= " AND t.id = ?";
            $params[] = $tankId;
        }

        $sql .= " GROUP BY p.name ORDER BY total_amount DESC";

        return DB::select($sql, $params);
    }
}