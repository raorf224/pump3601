<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;



class StationController extends Controller
{
    public function get()
    {
        $fetch = DB::select('SELECT * FROM stations');
        return response()->json($fetch);
    }

    public function store(Request $request)
    {
        // 🧩 Normalize product keys (agar frontend se aaye to)
        if ($request->has('products')) {
            $products = $request->products;
            foreach ($products as $key => $p) {
                if (isset($p['productid']) && !isset($p['product_id'])) {
                    $products[$key]['product_id'] = $p['productid'];
                }
            }
            $request->merge(['products' => $products]);
        }

        // ✅ Validation rules - products ko nullable rakhen
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
			'working_capital' => 'nullable|string|max:255',
            'coordinates' => 'nullable|string',
            'products' => 'nullable|array', // 👈 Products optional
            'products.*.product_id' => 'nullable|exists:products,id', // 👈 nullable
            'products.*.price' => 'nullable|numeric|min:0',
            'products.*.effective_from' => 'nullable|date',
            'products.*.effective_to' => 'nullable|date|after_or_equal:products.*.effective_from',
        ]);

        // ✅ Extract lat/lng
        $lat = $request->lat;
        $lng = $request->lng;

        if ((!$lat || !$lng) && $request->filled('coordinates')) {
            $cleanCoords = preg_replace('/\s+/', '', $request->coordinates);
            $coords = explode(',', $cleanCoords);
            if (count($coords) === 2) {
                $lat = is_numeric($coords[0]) ? (float) $coords[0] : null;
                $lng = is_numeric($coords[1]) ? (float) $coords[1] : null;
            }
        }

        // ✅ Create new station
        $stationId = DB::table('stations')->insertGetId([
            'user_id' => $request->user_id,
            'id' => $request->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'location' => $request->location,
            'city' => $request->city,
			'local' => $request->has('local') ? 1 : 0,
            'lat' => $lat,
            'lng' => $lng,
			'working_capital' => $request->working_capital,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ✅ Insert products only if provided (OPTIONAL)
        if (!empty($request->products)) {
            foreach ($request->products as $product) {
                $productId = $product['product_id'] ?? null;
                $price = $product['price'] ?? null;
                $effectiveFrom = $product['effective_from'] ?? now();
                $effectiveTo = $product['effective_to'] ?? null;

                if ($productId) {
                    // Insert into station_products
                    $stationProductId = DB::table('station_products')->insertGetId([
                        'station_id' => $stationId,
                        'product_id' => $productId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Insert into product_prices
                    DB::table('product_prices')->insert([
                        'station_product_id' => $stationProductId,
                        'price' => $price,
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // ✅ Fetch the station
        $station = DB::table('stations')->where('id', $stationId)->first();
        
        // Products optional hain, agar hain to fetch karen
        $station->products = [];
        if (DB::table('station_products')->where('station_id', $stationId)->exists()) {
            $station->products = DB::table('station_products')
                ->join('products', 'station_products.product_id', '=', 'products.id')
                ->leftJoin('product_prices', 'station_products.id', '=', 'product_prices.station_product_id')
                ->select(
                    'products.id',
                    'products.name',
                    'station_products.id as station_product_id',
                    'product_prices.price',
                    'product_prices.effective_from',
                    'product_prices.effective_to'
                )
                ->where('station_products.station_id', $stationId)
                ->get();
        }

        return response()->json([
            'message' => '✅ Station created successfully!',
            'data' => $station
        ], 201);
    }




    // For all super admin to see all stations
    public function index()
    {
        $stations = DB::select('
        SELECT s.*, 
            u.username,
            u.full_name,
            u.email,
            u.phone,
            u.role,
            (SELECT COUNT(*) FROM employees e WHERE e.station_id = s.id AND e.status = "active") AS employees_count,
            (SELECT IFNULL(SUM(capacity), 0) FROM tanks t WHERE t.station_id = s.id) AS total_capacity
        FROM stations s
        LEFT JOIN users u ON s.user_id = u.id OR  s.user_id = u.stationrow_id
    ');

        return response()->json($stations, 200);
    }

    // For user to see their stations only
    public function index1($user_id)
    {
        $stations = DB::select('
        SELECT s.*, 
            u.username,
            u.full_name,
            u.email,
            u.phone,
            u.role,
            (SELECT COUNT(*) FROM employees e WHERE e.station_id = s.id AND e.status = "active") AS employees_count,
            (SELECT IFNULL(SUM(capacity), 0) FROM tanks t WHERE t.station_id = s.id) AS total_capacity
        FROM stations s
        LEFT JOIN users u ON s.user_id = u.id OR  s.user_id = u.stationrow_id
        WHERE s.user_id = ?
    ', [$user_id]);

        return response()->json($stations, 200);
    }

    public function show_emp($user_id)
    {
        $stations = DB::select('
        SELECT s.*, 
            u.username,
            u.full_name,
            u.email,
            u.phone,
            u.role,
            e.role AS designation,
            
            (SELECT COUNT(*) FROM employees e WHERE e.station_id = s.id AND e.status = "active") AS employees_count,
            (SELECT IFNULL(SUM(capacity), 0) FROM tanks t WHERE t.station_id = s.id) AS total_capacity
        FROM stations s
        LEFT JOIN users u ON s.user_id = u.id OR  s.user_id = u.stationrow_id
        LEFT JOIN employees e ON e.station_id = s.id OR e.station_id = s.id
        WHERE e.user_id = ?
    ', [$user_id]);

        return response()->json($stations, 200);
    }

    public function show($id)
    {
        $station = DB::select('
        SELECT 
            s.*, 
            u.username, 
            u.full_name, 
            u.email, 
            u.role
        FROM stations s
        LEFT JOIN users u ON s.user_id = u.id OR  s.user_id = u.stationrow_id
        WHERE s.id = ?
    ', [$id]);

        if (empty($station)) {
            return response()->json(['message' => 'Station not found'], 404);
        }

        // ✅ Add combined coordinate string for frontend
        $station[0]->coordinates = $station[0]->lat . ',' . $station[0]->lng;

        return response()->json($station[0], 200);
    }


    // UPDATE StationController - fix the stations_product method
    public function stations_product($id)
    {
        $products = DB::select('
        SELECT 
            p.*,
            sp.id as station_product_id,
            pp.price,
            pp.effective_from,
            pp.effective_to
        FROM `station_products` sp 
        JOIN products p ON sp.product_id = p.id 
        LEFT JOIN product_prices pp ON sp.id = pp.station_product_id
        WHERE sp.station_id = ?
        ORDER BY pp.effective_from DESC
    ', [$id]);

        if (!$products) {
            return response()->json([], 200);
        }

        return response()->json($products, 200);
    }

// UPDATE StationController
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $station = DB::table('stations')->where('id', $id)->first();
        if (!$station) {
            return response()->json(['message' => 'Station not found'], 404);
        }

        // ✅ Parse coordinates
        $lat = $data['lat'] ?? null;
        $lng = $data['lng'] ?? null;
        if (!empty($data['coordinates'])) {
            $coords = explode(',', $data['coordinates']);
            if (count($coords) === 2) {
                $lat = trim($coords[0]);
                $lng = trim($coords[1]);
            }
        }

        // ✅ Update station info
        DB::table('stations')->where('id', $id)->update([
            'name' => $data['name'] ?? $station->name,
            'phone' => $data['phone'] ?? $station->phone,
            'location' => $data['location'] ?? $station->location,
            'city' => $data['city'] ?? $station->city,
			'local' => $request->has('local') ? 1 : 0,
            'lat' => $lat ?? $station->lat,
            'lng' => $lng ?? $station->lng,
            'working_capital' => $data['working_capital'] ?? $station->working_capital,
            'updated_at' => now(),
        ]);

        // ✅ Update products ONLY if provided
        if (!empty($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as $p) {
                $productId = $p['product_id'] ?? $p['productid'] ?? null;
                if (!$productId)
                    continue;

                $price = $p['price'] ?? 0;
                $effectiveFrom = $p['effective_from'] ?? now();
                $effectiveTo = $p['effective_to'] ?? null;

                $existing = DB::table('station_products')
                    ->where('station_id', $id)
                    ->where('product_id', $productId)
                    ->first();

                if ($existing) {
                    DB::table('product_prices')
                        ->where('station_product_id', $existing->id)
                        ->update([
                            'price' => $price,
                            'effective_from' => $effectiveFrom,
                            'effective_to' => $effectiveTo,
                            'updated_at' => now(),
                        ]);
                } else {
                    $stationProductId = DB::table('station_products')->insertGetId([
                        'station_id' => $id,
                        'product_id' => $productId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('product_prices')->insert([
                        'station_product_id' => $stationProductId,
                        'price' => $price,
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $updatedStation = DB::table('stations')->where('id', $id)->first();

        return response()->json([
            'message' => 'Station updated successfully!',
            'data' => $updatedStation
        ]);
    }


    public function destroy($id)
    {
        $deleted = DB::delete('DELETE FROM stations WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(
                ['message' => 'Station deleted successfully'],
                200 // ✅ OK
            );
        }

        return response()->json(
            ['message' => 'Station not found'],
            404 // ❌ Not Found
        );
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        $updated = DB::update('UPDATE stations SET status = ? WHERE id = ?', [
            $request->status,
            $id
        ]);

        if ($updated) {
            return response()->json([
                'message' => 'Station status updated successfully'
            ], 200);
        }

        return response()->json([
            'message' => 'Station not found or status unchanged'
        ], 404);
    }

	public function stationsForEmployee($user_id)
{
    $stations = DB::select('
        SELECT s.* 
        FROM stations s
        INNER JOIN employees e ON s.id = e.station_id 
        WHERE e.user_id = ? AND e.status = "active"
    ', [$user_id]);

    return response()->json($stations, 200);
}
	
public function currentstatus1($stationId)
{
    // =========================
    // DATE RANGES
    // =========================
    $startCurrent = now()->startOfMonth()->format('Y-m-d');
    $endCurrent   = now()->endOfMonth()->format('Y-m-d');
    $startPrev = now()->subMonth()->startOfMonth()->format('Y-m-d');
    $endPrev   = now()->subMonth()->endOfMonth()->format('Y-m-d');

    // =========================
    // STATIONS / INVENTORY
    // =========================
    $stations = DB::select('
        SELECT 
            SUM(t.current_level) AS total_current_level,
            fl.rate,
            p.name
        FROM tanks t
        JOIN products p ON t.product_id = p.id
        LEFT JOIN fuel_inventory_layers fl 
            ON fl.id = (
                SELECT id FROM fuel_inventory_layers
                WHERE tank_id = t.id 
                AND product_id = t.product_id
                ORDER BY created_at DESC 
                LIMIT 1
            )
        WHERE t.station_id = ?
        GROUP BY t.product_id, p.name, fl.rate
    ', [$stationId]);

    // =========================
    // TRANSIT
    // =========================
    $transit = DB::select('
        SELECT * 
        FROM oil_purchase o 
        JOIN ammount_paid ap ON o.id = ap.oil_purchase_id 
        JOIN products p ON p.id = o.product_id
        WHERE o.station_id = ?
        AND o.recive_status = "Not-Recived"
    ', [$stationId]);

    // =========================
    // LUBE DOCUMENTS
    // =========================
    $lube = DB::select('
        SELECT ll.*, p.name 
        FROM lube_lines ll 
        JOIN lube_documents ld ON ll.document_id = ld.id 
        JOIN products p ON ll.product_id = p.id 
        WHERE ld.doc_type = "purchase"
        AND ld.station_id = ?
    ', [$stationId]);

    // =========================
    // CASH IN HAND
    // =========================
    $cashinhand = DB::select('
        SELECT * 
        FROM shifts 
        WHERE station_id = ? 
        ORDER BY id DESC 
        LIMIT 1
    ', [$stationId]);

    // =========================
    // GIVEN TO KHATTAK
    // =========================
    $given_to_khattak = DB::select('
        SELECT a.name, a.type, SUM(c.amount) as total 
        FROM credit_driver c 
        JOIN accounts a ON c.account_id = a.id 
        WHERE c.station_id = ? 
        AND c.created_at >= DATE_FORMAT(CURDATE(), "%Y-%m-01")
        AND c.created_at <= DATE_ADD(DATE_FORMAT(CURDATE(), "%Y-%m-01"), INTERVAL 1 MONTH)  
        GROUP BY c.account_id, a.name, a.type
    ', [$stationId]);

    // =========================
    // IN BANK
    // =========================
    $in_bank = DB::select('
        SELECT * FROM site_total_ammount WHERE station_id = ? ORDER BY id DESC LIMIT 1
    ', [$stationId]);

    // =========================
    // IN HAND
    // =========================
    $in_hand = DB::select('
        SELECT * FROM shifts WHERE station_id = ? ORDER BY id DESC LIMIT 1
    ', [$stationId]);

    // =========================
    // SHORTAGES
    // =========================
    $shortages = DB::select('
        SELECT 
            o.product_id,
            p.name,
            (SUM(ort.shortage) * o.rate) as amount,
            SUM(ort.shortage) AS total_shortage
        FROM oil_purchase o
        JOIN oil_recived_tanks ort ON o.id = ort.oil_purchase_id
        JOIN products p ON o.product_id = p.id 
        LEFT JOIN shortage_ammount_paid_back sp ON sp.oil_purchase_id = o.id
        WHERE o.station_id = ?
        AND sp.oil_purchase_id IS NULL
        GROUP BY o.product_id, p.name, o.rate
    ', [$stationId]);

    // =========================
    // GAIN / LOSS (CURRENT MONTH)
    // =========================
    $gainLossRaw = DB::select("
        SELECT
            p.name AS product_name,
            t.id AS tank_id,
            s.id AS shift_id,
            s.shift_no,
            s.start_time,
            s.end_time,
            td.dip_in_liters AS dip_liters,
            td.old_dip_liters AS old_dip_liters,
            COALESCE(SUM(snr.total_dispensed), 0) AS total_dispensed,
            ((td.old_dip_liters - td.dip_in_liters) - COALESCE(SUM(snr.total_dispensed), 0)) AS variance
        FROM shifts s
        JOIN tanks_dip td ON td.shift_id = s.id
        JOIN tanks t ON t.id = td.tank_id
        JOIN products p ON p.id = t.product_id
        LEFT JOIN nozzles n ON n.tank_id = t.id
        LEFT JOIN shift_nozzle_readings snr ON snr.shift_id = s.id AND snr.nozzle_id = n.id
        WHERE s.station_id = ?
        AND DATE(s.start_time) BETWEEN ? AND ?
        GROUP BY p.name, t.id, s.id, s.shift_no, s.start_time, s.end_time, td.dip_in_liters, td.old_dip_liters
    ", [$stationId, $startCurrent, $endCurrent]);

    // =========================
    // PRODUCT WISE GAIN / LOSS SUMMARY
    // =========================
    $gainLossSummary = [];
    foreach ($gainLossRaw as $row) {
        $product = $row->product_name;
        if (!isset($gainLossSummary[$product])) {
            $gainLossSummary[$product] = [
                'product' => $product,
                'total_gain' => 0,
                'total_loss' => 0,
                'net_variance' => 0
            ];
        }
        $variance = (float) $row->variance;
        if ($variance > 0) {
            $gainLossSummary[$product]['total_gain'] += $variance;
        }
        if ($variance < 0) {
            $gainLossSummary[$product]['total_loss'] += abs($variance);
        }
        $gainLossSummary[$product]['net_variance'] += $variance;
    }
    $gainLossSummary = array_values($gainLossSummary);

    // =========================
    // PROFIT DATA (CURRENT & PREVIOUS)
    // =========================
    $current = DB::select("
        SELECT fc.profit
        FROM tanks t
        JOIN fuel_inventory_layers f ON t.id = f.tank_id
        JOIN fuel_layer_consumptions fc ON f.id = fc.layer_id
        WHERE t.station_id = ?
        AND fc.created_at BETWEEN ? AND ?
    ", [$stationId, $startCurrent, $endCurrent]);

    $previous = DB::select("
        SELECT fc.profit
        FROM tanks t
        JOIN fuel_inventory_layers f ON t.id = f.tank_id
        JOIN fuel_layer_consumptions fc ON f.id = fc.layer_id
        WHERE t.station_id = ?
        AND fc.created_at BETWEEN ? AND ?
    ", [$stationId, $startPrev, $endPrev]);

    // =========================
    // EXPENSE DATA
    // =========================
    $currentExpense = DB::select("
        SELECT SUM(debit) as total
        FROM transactions
        WHERE station_id = ?
        AND type = 'expense'
        AND created_at BETWEEN ? AND ?
    ", [$stationId, $startCurrent, $endCurrent]);

    $previousExpense = DB::select("
        SELECT SUM(debit) as total
        FROM transactions
        WHERE station_id = ?
        AND type = 'expense'
        AND created_at BETWEEN ? AND ?
    ", [$stationId, $startPrev, $endPrev]);

    // =========================
    // WORKING CAPITAL
    // =========================
    $Workingcapital = DB::select("SELECT * FROM stations WHERE id = ?", [$stationId]);

    // =========================
    // FUEL CARD
    // =========================
    $fuelcard = DB::select("
        SELECT 
            sc.faccountid AS account_id,
            a.name AS account_name,
            a.mdr,
            SUM(IFNULL(sc.fuelcard, 0)) AS total_fuel_card,
            (SUM(IFNULL(sc.fuelcard, 0)) * IFNULL(a.mdr, 0) / 100) AS fuel_card_mdr
        FROM shift_cash_flow sc
        JOIN accounts a ON a.id = sc.faccountid
        JOIN shifts s ON sc.shift_id = s.id
        WHERE IFNULL(sc.fuel_card_paid, '0') != '1'
        AND sc.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        AND sc.created_at <= DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH) 
        AND s.station_id = ?
        GROUP BY sc.faccountid, a.name, a.mdr
    ", [$stationId]);

    // =========================
    // CREDIT CARD
    // =========================
    $creditcard = DB::select("
        SELECT 
            sc.caccountid AS account_id,
            a.name AS account_name,
            a.mdr,
            SUM(IFNULL(sc.creditcard, 0)) AS total_credit_card,
            (SUM(IFNULL(sc.creditcard, 0)) * IFNULL(a.mdr, 0) / 100) AS credit_card_mdr
        FROM shift_cash_flow sc
        JOIN accounts a ON a.id = sc.caccountid
        JOIN shifts s ON sc.shift_id = s.id
        WHERE IFNULL(sc.credit_card_paid, '0') != '1' 
        AND s.station_id = ?
        GROUP BY sc.caccountid, a.name, a.mdr
    ", [$stationId]);

    // =========================
    // HELPERS
    // =========================
    $calcProfit = function ($data) {
        $profit = 0;
        $loss = 0;
        foreach ($data as $row) {
            $val = (float) $row->profit;
            if ($val >= 0) {
                $profit += $val;
            } else {
                $loss += $val;
            }
        }
        return [
            'profit' => $profit,
            'loss' => abs($loss),
            'net' => $profit + $loss,
        ];
    };
    
    $getExpense = fn($data) => $data[0]->total ?? 0;

    // =========================
    // RESPONSE
    // =========================
    return response()->json([
        'stations' => $stations,
        'transit' => $transit,
        'lube' => $lube,
        'in_hand' => $in_hand,
        'cashinhand' => $cashinhand,
        'in_bank' => $in_bank,
        'giventokhattak' => $given_to_khattak,
        'fuelcard' => $fuelcard,
        'creditcard' => $creditcard,
        'Workingcapital' => $Workingcapital,
        'gain_loss' => [
            'summary' => array_values($gainLossSummary),
            'raw' => $gainLossRaw,
        ],
        'current_month' => $calcProfit($current),
        'previous_month' => $calcProfit($previous),
        'expense' => [
            'current_month' => $getExpense($currentExpense),
            'previous_month' => $getExpense($previousExpense),
        ],
        'range' => [
            'current' => [$startCurrent, $endCurrent],
            'previous' => [$startPrev, $endPrev],
        ],
    ]);
}

public function currentstatus($stationId, $format = 'json')
{
    // =========================
    // DATE RANGES
    // =========================
    $startCurrent = now()->startOfMonth()->format('Y-m-d');
    $endCurrent   = now()->endOfMonth()->format('Y-m-d');
    $startPrev    = now()->subMonth()->startOfMonth()->format('Y-m-d');
    $endPrev      = now()->subMonth()->endOfMonth()->format('Y-m-d');

    // =========================
    // 1. WORKING CAPITAL & STATION NAME
    // =========================
    $stationData = DB::table('stations')->where('id', $stationId)->select('working_capital', 'name')->first();
    $workingCapital = $stationData->working_capital ?? 0;
    $stationName = $stationData->name ?? 'Station #' . $stationId;

    // =========================
    // 2. FUEL STOCK (TANKS) - Convert to array
    // =========================
    $fuelStock = DB::table('tanks as t')
        ->select([
            'p.name',
            DB::raw('SUM(t.current_level) as total_current_level'),
            DB::raw("COALESCE(
                (SELECT rate FROM fuel_inventory_layers WHERE tank_id = t.id ORDER BY created_at DESC LIMIT 1),
                (SELECT rate FROM oil_purchase WHERE station_id = {$stationId} AND product_id = t.product_id ORDER BY id DESC LIMIT 1),
                0
            ) as rate")
        ])
        ->join('products as p', 't.product_id', '=', 'p.id')
        ->where('t.station_id', $stationId)
        ->where('t.status', 'active')
        ->groupBy('p.name', 't.product_id','t.id')
        ->get()
        ->map(function($item) {
            return [
                'name' => $item->name,
                'total_current_level' => (float) $item->total_current_level,
                'rate' => (float) $item->rate
            ];
        })
        ->toArray();

    // =========================
    // 3. STOCK IN TRANSIT - Convert to array
    // =========================
    $transit = DB::select("
        SELECT p.name, op.qty, op.rate, (op.qty * op.rate) AS amount
        FROM oil_purchase op
        JOIN products p ON op.product_id = p.id
        WHERE op.station_id = ? AND op.recive_status = 'Not-Recived'
    ", [$stationId]);
    
    $transit = array_map(function($item) {
        return [
            'name' => $item->name,
            'qty' => (float) $item->qty,
            'rate' => (float) $item->rate,
            'amount' => (float) $item->amount
        ];
    }, $transit);

    // =========================
    // 4. GIVEN TO KHATTAK (Current Month) - Convert to array
    // =========================
    $givenToKhattak = DB::select("
        SELECT a.name, SUM(cd.amount) AS total
        FROM credit_driver cd
        JOIN accounts a ON cd.account_id = a.id
        WHERE cd.station_id = ?
        AND DATE(cd.created_at) BETWEEN ? AND ?
        GROUP BY a.name
    ", [$stationId, $startCurrent, $endCurrent]);
    
    $givenToKhattak = array_map(function($item) {
        return [
            'name' => $item->name,
            'total' => (float) $item->total
        ];
    }, $givenToKhattak);

    // =========================
    // 5. CASH IN SAFE (BANK)
    // =========================
    $totalBankAmount = DB::selectOne("
        SELECT SUM(amount) as total FROM (
            SELECT amount FROM site_total_ammount 
            WHERE station_id = ? 
            ORDER BY id DESC
        ) as sub
    ", [$stationId])->total ?? 0;

    // =========================
    // 6. CASH IN HAND
    // =========================
    $latestShift = DB::selectOne("
        SELECT status, cash_handover, cash_return 
        FROM shifts 
        WHERE station_id = ? 
        ORDER BY id DESC 
        LIMIT 1
    ", [$stationId]);
    
    $cashInHand = 0;
    if ($latestShift) {
        $cashInHand = ($latestShift->status == 'open') ? $latestShift->cash_handover : $latestShift->cash_return;
    }

    // =========================
    // 7. GAIN/LOSS CURRENT MONTH - Convert to array
    // =========================
    $currentGainLoss = DB::select("
        SELECT 
            p.name AS product_name,
            SUM(CASE 
                WHEN (td.old_dip_liters - td.dip_in_liters) - COALESCE(snr.total_dispensed, 0) > 0
                THEN (td.old_dip_liters - td.dip_in_liters) - COALESCE(snr.total_dispensed, 0)
                ELSE 0 
            END) AS total_loss,
            SUM(CASE 
                WHEN (td.old_dip_liters - td.dip_in_liters) - COALESCE(snr.total_dispensed, 0) < 0
                THEN ABS((td.old_dip_liters - td.dip_in_liters) - COALESCE(snr.total_dispensed, 0))
                ELSE 0 
            END) AS total_gain,
            COALESCE(pp.price, 0) AS rate
        FROM shifts s
        JOIN tanks_dip td ON td.shift_id = s.id
        JOIN tanks t ON t.id = td.tank_id
        JOIN products p ON p.id = t.product_id
        LEFT JOIN (
            SELECT 
                snr.shift_id,
                n.tank_id,
                SUM(snr.total_dispensed) AS total_dispensed
            FROM shift_nozzle_readings snr
            JOIN nozzles n ON n.id = snr.nozzle_id
            GROUP BY snr.shift_id, n.tank_id
        ) snr ON snr.shift_id = s.id AND snr.tank_id = t.id
        LEFT JOIN station_products sp ON sp.station_id = s.station_id AND sp.product_id = t.product_id
        LEFT JOIN product_prices pp ON pp.station_product_id = sp.id
        WHERE s.station_id = ?
            AND DATE(s.start_time) BETWEEN ? AND ?
        GROUP BY p.name, pp.price
    ", [$stationId, $startCurrent, $endCurrent]);

    $currentLossAmount = 0;
    $currentGainAmount = 0;
    $gainLossDetails = [];
    foreach ($currentGainLoss as $row) {
        $lossAmount = $row->total_loss * $row->rate;
        $gainAmount = $row->total_gain * $row->rate;
        $currentLossAmount += $lossAmount;
        $currentGainAmount += $gainAmount;
        
        $gainLossDetails[] = [
            'product_name' => $row->product_name,
            'total_loss' => (float) $row->total_loss,
            'total_gain' => (float) $row->total_gain,
            'rate' => (float) $row->rate,
            'loss_amount' => $lossAmount,
            'gain_amount' => $gainAmount
        ];
    }

    // =========================
    // 7b. GAIN/LOSS PREVIOUS MONTH - Convert to array
    // =========================
    $previousGainLoss = DB::select("
        SELECT 
            p.name AS product_name,
            SUM(CASE 
                WHEN (td.old_dip_liters - td.dip_in_liters) - COALESCE(snr.total_dispensed, 0) > 0
                THEN (td.old_dip_liters - td.dip_in_liters) - COALESCE(snr.total_dispensed, 0)
                ELSE 0 
            END) AS total_loss,
            SUM(CASE 
                WHEN (td.old_dip_liters - td.dip_in_liters) - COALESCE(snr.total_dispensed, 0) < 0
                THEN ABS((td.old_dip_liters - td.dip_in_liters) - COALESCE(snr.total_dispensed, 0))
                ELSE 0 
            END) AS total_gain,
            COALESCE(pp.price, 0) AS rate
        FROM shifts s
        JOIN tanks_dip td ON td.shift_id = s.id
        JOIN tanks t ON t.id = td.tank_id
        JOIN products p ON p.id = t.product_id
        LEFT JOIN (
            SELECT 
                snr.shift_id,
                n.tank_id,
                SUM(snr.total_dispensed) AS total_dispensed
            FROM shift_nozzle_readings snr
            JOIN nozzles n ON n.id = snr.nozzle_id
            GROUP BY snr.shift_id, n.tank_id
        ) snr ON snr.shift_id = s.id AND snr.tank_id = t.id
        LEFT JOIN station_products sp ON sp.station_id = s.station_id AND sp.product_id = t.product_id
        LEFT JOIN product_prices pp ON pp.station_product_id = sp.id
        WHERE s.station_id = ?
            AND DATE(s.start_time) BETWEEN ? AND ?
        GROUP BY p.name, pp.price
    ", [$stationId, $startPrev, $endPrev]);
    
    $previousGainLoss = array_map(function($item) {
        return [
            'product_name' => $item->product_name,
            'total_loss' => (float) $item->total_loss,
            'total_gain' => (float) $item->total_gain,
            'rate' => (float) $item->rate
        ];
    }, $previousGainLoss);

    // =========================
    // 8. LUBE INVENTORY - Convert to array
    // =========================
    $lubeInventory = DB::select("
        SELECT p.name, li.quantity, li.avg_buying_price, (li.quantity * li.avg_buying_price) AS total_amount
        FROM lube_inventory li
        JOIN products p ON li.product_id = p.id
        WHERE li.station_id = ? AND li.quantity > 0
    ", [$stationId]);
    
    $lubeInventory = array_map(function($item) {
        return [
            'name' => $item->name,
            'quantity' => (float) $item->quantity,
            'avg_buying_price' => (float) $item->avg_buying_price,
            'total_amount' => (float) $item->total_amount
        ];
    }, $lubeInventory);

    // =========================
    // 9. EXPENSES
    // =========================
    $currentExpense = DB::selectOne("
        SELECT COALESCE(SUM(debit), 0) AS total 
        FROM transactions 
        WHERE station_id = ? AND type = 'expense' AND is_testing = 0 
        AND DATE(created_at) BETWEEN ? AND ?
    ", [$stationId, $startCurrent, $endCurrent]);

    $previousExpense = DB::selectOne("
        SELECT COALESCE(SUM(debit), 0) AS total 
        FROM transactions 
        WHERE station_id = ? AND type = 'expense' AND is_testing = 0 
        AND DATE(created_at) BETWEEN ? AND ?
    ", [$stationId, $startPrev, $endPrev]);

    // =========================
    // 10. FUEL CARD PENDING - Convert to array
    // =========================
    $fuelCardPending = DB::select("
        SELECT a.name AS account_name, a.mdr, COALESCE(SUM(sc.fuelcard), 0) AS total_fuel_card
        FROM shift_cash_flow sc
        JOIN accounts a ON a.id = sc.faccountid
        JOIN shifts s ON sc.shift_id = s.id
        WHERE IFNULL(sc.fuel_card_paid, '0') != '1' AND s.station_id = ?
        GROUP BY a.name, a.mdr
    ", [$stationId]);
    
    $fuelCardPending = array_map(function($item) {
        $total = (float) $item->total_fuel_card;
        $mdr = (float) $item->mdr;
        return [
            'account_name' => $item->account_name,
            'mdr' => $mdr,
            'total_fuel_card' => $total,
            'fuel_card_mdr' => ($total * $mdr) / 100
        ];
    }, $fuelCardPending);

    // =========================
    // 11. CREDIT CARD PENDING - Convert to array
    // =========================
    $creditCardPending = DB::select("
        SELECT a.name AS account_name, a.mdr, COALESCE(SUM(sc.creditcard), 0) AS total_credit_card
        FROM shift_cash_flow sc
        JOIN accounts a ON a.id = sc.caccountid
        JOIN shifts s ON sc.shift_id = s.id
        WHERE IFNULL(sc.credit_card_paid, '0') != '1' AND s.station_id = ?
        GROUP BY a.name, a.mdr
    ", [$stationId]);
    
    $creditCardPending = array_map(function($item) {
        $total = (float) $item->total_credit_card;
        $mdr = (float) $item->mdr;
        return [
            'account_name' => $item->account_name,
            'mdr' => $mdr,
            'total_credit_card' => $total,
            'credit_card_mdr' => ($total * $mdr) / 100
        ];
    }, $creditCardPending);

    // =========================
    // 12. ENGINEERING OUTSTANDING
    // =========================
    $engineeringOutstanding = DB::selectOne("
        SELECT COALESCE(SUM(credit), 0) AS total 
        FROM transactions 
        WHERE station_id = ? AND is_testing = 1
    ", [$stationId]);

    // =========================
    // 13. SHORTAGES PENDING - Convert to array
    // =========================
    $shortagesPending = DB::select("
        SELECT p.name, SUM(ort.shortage) AS total_shortage, (SUM(ort.shortage) * op.rate) AS amount
        FROM oil_recived_tanks ort
        JOIN oil_purchase op ON ort.oil_purchase_id = op.id
        JOIN products p ON op.product_id = p.id
        LEFT JOIN shortage_ammount_paid_back sp ON sp.oil_purchase_id = op.id
        WHERE op.station_id = ? AND ort.shortage > 0 AND sp.id IS NULL
        GROUP BY p.name, op.rate
    ", [$stationId]);
    
    $shortagesPending = array_map(function($item) {
        return [
            'name' => $item->name,
            'total_shortage' => (float) $item->total_shortage,
            'amount' => (float) $item->amount
        ];
    }, $shortagesPending);
    
    $totalShortages = array_sum(array_column($shortagesPending, 'amount'));

    // =========================
    // 14. CALCULATE TOTAL OUTFLOW
    // =========================
    $totalFuelCard = array_sum(array_column($fuelCardPending, 'total_fuel_card'));
    $totalFuelCardMDR = array_sum(array_column($fuelCardPending, 'fuel_card_mdr'));
    $totalCreditCard = array_sum(array_column($creditCardPending, 'total_credit_card'));
    $totalCreditCardMDR = array_sum(array_column($creditCardPending, 'credit_card_mdr'));
    
    $totalOutflow = ($currentExpense->total ?? 0) 
                    + ($previousExpense->total ?? 0) 
                    + $totalFuelCard + $totalFuelCardMDR
                    + $totalCreditCard + $totalCreditCardMDR
                    + $currentLossAmount 
                    + $totalShortages 
                    + ($engineeringOutstanding->total ?? 0);
    
    $remainingAmount = $totalOutflow - $workingCapital;

    // =========================
    // PREPARE DATA ARRAY (for both JSON and PDF)
    // =========================
    $data = [
        'station_id' => $stationId,
        'station_name' => $stationName,  // <-- Station name added here
        'working_capital' => (float) $workingCapital,
        'total_outflow' => (float) $totalOutflow,
        'remaining' => [
            'amount' => abs($remainingAmount),
            'status' => $totalOutflow >= $workingCapital ? 'profit' : 'loss',
            'color' => $totalOutflow >= $workingCapital ? 'green' : 'red',
            'recovered_percentage' => round(($totalOutflow / $workingCapital) * 100, 2),
            'remaining_to_recover' => $totalOutflow < $workingCapital ? ($workingCapital - $totalOutflow) : 0
        ],
        'fuel_stock' => $fuelStock,
        'stock_in_transit' => $transit,
        'given_to_khattak' => $givenToKhattak,
        'cash_in_safe' => (float) $totalBankAmount,
        'cash_in_hand' => (float) $cashInHand,
        'gain_loss_current' => [
            'loss_amount' => (float) $currentLossAmount,
            'gain_amount' => (float) $currentGainAmount,
            'net' => (float) ($currentLossAmount - $currentGainAmount),
            'details' => $gainLossDetails
        ],
        'gain_loss_previous' => $previousGainLoss,
        'lube_inventory' => $lubeInventory,
        'expense_current' => (float) ($currentExpense->total ?? 0),
        'expense_previous' => (float) ($previousExpense->total ?? 0),
        'fuel_card_pending' => $fuelCardPending,
        'credit_card_pending' => $creditCardPending,
        'engineering_outstanding' => (float) ($engineeringOutstanding->total ?? 0),
        'shortages_pending' => $shortagesPending,
        'total_shortages' => (float) $totalShortages,
        'date_ranges' => [
            'current' => [$startCurrent, $endCurrent],
            'previous' => [$startPrev, $endPrev],
        ],
    ];

    // =========================
    // RETURN PDF OR JSON
    // =========================
if ($format == 'pdf') {
    $pdf = \PDF::loadView('audit-pdf', ['data' => $data]);
    return $pdf->download('audit-report-station-' . $stationId . '.pdf');
}

    return response()->json($data);
}
	
	public function downloadAuditReport($stationId)
{
    return $this->currentstatus($stationId, 'pdf');
}

public function generateAuditPdf($stationId)
{
    return $this->currentstatus($stationId, 'pdf');
}

}

