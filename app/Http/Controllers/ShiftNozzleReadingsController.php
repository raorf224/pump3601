<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftNozzleReadingsController extends Controller
{
    // Get all shift nozzle readings with full details (joins with shifts, nozzles, and stations)
    public function index()
    {
        $readings = DB::select(
            'SELECT snr.id, snr.opening_reading, snr.closing_reading, snr.total_dispensed, snr.rate, snr.total_amount, 
                    snr.created_at, snr.updated_at,
                    s.id AS shift_id, s.shift_no AS shift_name, 
                    n.id AS nozzle_id, n.name AS nozzle_name,
                    t.id AS tank_id, t.name AS tank_name,
                    st.id AS station_id, st.name AS station_name, st.location AS station_location
             FROM shift_nozzle_readings snr
             LEFT JOIN shifts s ON snr.shift_id = s.id OR snr.shift_id = s.stationrow_id
             LEFT JOIN nozzles n ON snr.nozzle_id = n.id OR snr.nozzle_id = n.stationrow_id
             LEFT JOIN tanks t ON n.tank_id = t.id OR n.tank_id = t.stationrow_id
             LEFT JOIN stations st ON t.station_id = st.id
             ORDER BY snr.created_at DESC'
        );

        return response()->json($readings);
    }

    // Get shift nozzle readings for a specific station
    public function getByStation($stationId)
    {
        $stationrec=DB::select("select * from stations where id=?",[$stationId]);
        if($stationrec[0]->local =="1"){

       
        $readings = DB::select(
            'SELECT snr.id, snr.opening_reading, snr.closing_reading, snr.total_dispensed, snr.rate, snr.total_amount, 
                    snr.created_at, snr.updated_at,
                    s.id AS shift_id, s.shift_no AS shift_name, 
                    n.id AS nozzle_id, n.name AS nozzle_name,
                    t.id AS tank_id, t.name AS tank_name,
                    n.product_id as product_id,
                    p.name as product_name,
                    st.id AS station_id, st.name AS station_name, st.location AS station_location
             FROM shift_nozzle_readings snr
             LEFT JOIN shifts s ON snr.shift_id = s.stationrow_id
             LEFT JOIN nozzles n ON snr.nozzle_id = n.stationrow_id
             LEFT JOIN tanks t ON n.tank_id = t.stationrow_id
             LEFT JOIN stations st ON t.station_id = st.id
             left join products p on n.product_id = p.id
             WHERE st.id = ?
             ORDER BY snr.created_at DESC',
            [$stationId]
        );
         }else{
            
        $readings = DB::select(
            'SELECT snr.id, snr.opening_reading, snr.closing_reading, snr.total_dispensed, snr.rate, snr.total_amount, 
                    snr.created_at, snr.updated_at,
                    s.id AS shift_id, s.shift_no AS shift_name, 
                    n.id AS nozzle_id, n.name AS nozzle_name,
                    t.id AS tank_id, t.name AS tank_name,
                    n.product_id as product_id,
                    p.name as product_name,
                    st.id AS station_id, st.name AS station_name, st.location AS station_location
             FROM shift_nozzle_readings snr
             LEFT JOIN shifts s ON snr.shift_id = s.id
             LEFT JOIN nozzles n ON snr.nozzle_id = n.id
             LEFT JOIN tanks t ON n.tank_id = t.id
             LEFT JOIN stations st ON t.station_id = st.id
             left join products p on n.product_id = p.id
             WHERE st.id = ?
             ORDER BY snr.created_at DESC',
            [$stationId]
        );
         }

        return response()->json($readings);
    }

    // Create a new shift nozzle reading
    public function store1(Request $request)
    {
        $validatedData = $request->validate([
            'shift_id' => 'required|integer',
            'nozzle_id' => 'required|integer',
            'opening_reading' => 'required|numeric',
            'closing_reading' => 'nullable|numeric',
            'collected_from' => 'required|integer',
        ]);

        // Fetch the shift details to get the station and shift date
        $shift = DB::select('SELECT * FROM shifts WHERE id = ?', [$validatedData['shift_id']]);
        if (empty($shift)) {
            return response()->json(['message' => 'Shift not found'], 404);
        }
        $shiftDate = $shift[0]->start_time;
        $stationId = $shift[0]->station_id;

        // Fetch the nozzle details to get the tank and product
        $nozzle = DB::select('SELECT * FROM nozzles WHERE id = ?', [$validatedData['nozzle_id']]);
        if (empty($nozzle)) {
            return response()->json(['message' => 'Nozzle not found'], 404);
        }
        $tankId = $nozzle[0]->tank_id;

        // Fetch the tank details to get the product
        $tank = DB::select('SELECT * FROM tanks WHERE id = ?', [$tankId]);
        if (empty($tank)) {
            return response()->json(['message' => 'Tank not found'], 404);
        }
        $productId = $tank[0]->product_id;

        // Fetch the station_product_id for the given station and product
        $stationProduct = DB::select(
            'SELECT id FROM station_products WHERE station_id = ? AND product_id = ?',
            [$stationId, $productId]
        );
        if (empty($stationProduct)) {
            return response()->json(['message' => 'Station product not found'], 404);
        }
        $stationProductId = $stationProduct[0]->id;

        // Fetch the product price for the given station_product_id and shift date
        $productPrice = DB::select(
            'SELECT price FROM product_prices 
                    WHERE station_product_id = ? AND effective_from <= ? 
                    ORDER BY effective_from DESC LIMIT 1',
            [$stationProductId, $shiftDate]
        );

        // ✅ CHECK IF PRODUCT PRICE FOUND
        if (empty($productPrice)) {
            return response()->json(['message' => 'Product price not found for the given date'], 404);
        }

        $rate = $productPrice[0]->price; // ✅ RATE VARIABLE DEFINED
        

        // ✅ INSERT WITH RATE (but without total_dispensed and total_amount)
        DB::insert(
            'INSERT INTO shift_nozzle_readings (shift_id, nozzle_id, opening_reading, closing_reading, rate, collected_from, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $validatedData['shift_id'],
                $validatedData['nozzle_id'],
                $validatedData['opening_reading'],
                $validatedData['closing_reading'],
                $rate, // ✅ RATE USE KARO
                $validatedData['collected_from'],
            ]
        );

        // ✅ UPDATE NOZZLE INITIAL METER READING
        if (isset($validatedData['closing_reading']) && $validatedData['closing_reading'] > 0) {
            DB::table('nozzles')
                ->where('id', $validatedData['nozzle_id'])
                ->update([
                    'intial_meter_reading' => $validatedData['closing_reading'],
                    'updated_at' => now()
                ]);
        }

        return response()->json(['message' => 'Shift nozzle reading created successfully'], 201);
    }

public function store(Request $request)
{
    DB::beginTransaction();

    try {

        $validatedData = $request->validate([
            'shift_id' => 'required|integer',
            'nozzle_id' => 'required|integer',
            'opening_reading' => 'required|numeric',
            'closing_reading' => 'nullable|numeric',
            'collected_from' => 'required|integer',
			'testing' => 'integer',
        ]);

        // ==============================
        // GET SHIFT
        // ==============================
        $shift = DB::table('shifts')->where('id', $validatedData['shift_id'])->first();
        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }

        $shiftDate = $shift->start_time;
        $stationId = $shift->station_id;

        // ==============================
        // GET NOZZLE
        // ==============================
       $nozzle = DB::table('nozzles')
    ->join('products', 'nozzles.product_id', '=', 'products.id')
    ->where('nozzles.id', $validatedData['nozzle_id'])
    ->select(
        'nozzles.*',
        'products.name as product_name',
        'products.id as product_id'
    )
    ->first();        if (!$nozzle) {
            return response()->json(['message' => 'Nozzle not found'], 404);
        }

        $tankId = $nozzle->tank_id;

        // ==============================
        // GET TANK
        // ==============================
        $tank = DB::table('tanks')->where('id', $tankId)->first();
        if (!$tank) {
            return response()->json(['message' => 'Tank not found'], 404);
        }

        $productId = $tank->product_id;

        // ==============================
        // GET PRODUCT PRICE
        // ==============================
        $stationProduct = DB::table('station_products')
            ->where('station_id', $stationId)
            ->where('product_id', $productId)
            ->first();

        if (!$stationProduct) {
            return response()->json(['message' => 'Station product not found'], 404);
        }

        $productPrice = DB::table('product_prices')
            ->where('station_product_id', $stationProduct->id)
            ->where('effective_from', '<=', $shiftDate)
            ->orderByDesc('effective_from')
            ->first();

        if (!$productPrice) {
            return response()->json(['message' => 'Product price not found'], 404);
        }

        $saleRate = $productPrice->price;

        // ==============================
        // CALCULATE QTY
        // ==============================
        $qty = 0;

        if (!empty($validatedData['closing_reading'])) {
            $qty = $validatedData['closing_reading'] - $validatedData['opening_reading'];

            if ($qty < 0) {
                return response()->json(['message' => 'Closing reading must be greater than opening'], 400);
            }
        }

        // ==============================
        // FIFO CALCULATION
        // ==============================
        $remainingQty = $qty;
        $totalCost = 0;
	  
        $layers = DB::table('fuel_inventory_layers')
            ->where('tank_id', $tankId)
            ->where('product_id', $productId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        if ($layers->isEmpty()) {
            DB::rollBack();
            return response()->json(['message' => 'No stock available in FIFO layers']);
        }

        // ==============================
        // INSERT SALE FIRST (to get ID)
        // ==============================
        $saleId = DB::table('shift_nozzle_readings')->insertGetId([
            'shift_id' => $validatedData['shift_id'],
            'nozzle_id' => $validatedData['nozzle_id'],
            'opening_reading' => $validatedData['opening_reading'],
            'closing_reading' => $validatedData['closing_reading'],
            'rate' => $saleRate,
            'cost_amount' => 0,
            'profit' => 0,
            'collected_from' => $validatedData['collected_from'],
            'testing_reading' => $validatedData['testing'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
$testingamount = floatval($saleRate) * floatval($validatedData['testing']);
if($validatedData['testing']>0){
$stationsid = DB::select(
    "SELECT s.id 
     FROM shifts sf
     JOIN stations s ON sf.station_id = s.id
     WHERE sf.id = ?",
    [$validatedData['shift_id']]
);

DB::insert(
                    "INSERT INTO transactions
    (station_id, account_id, shift_id, type, debit, note,is_testing)
    VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $stationsid[0]->id,
                        "0",
                        $validatedData['shift_id'],
                        "Expense",
                        $testingamount,
                        "Testing Nozzle Amount For Nozzle $nozzle->name And Product $nozzle->product_name",
                        1
                    ]

);
}

        // ==============================
        // PROCESS FIFO LAYERS
        // ==============================
        foreach ($layers as $layer) {

            if ($remainingQty <= 0) break;

            $used = min($layer->remaining_qty, $remainingQty);

            $cost = $used * $layer->rate;
            $sale = $used * $saleRate;
            $profit = $sale - $cost;

            $totalCost += $cost;

            // 🔥 STORE LAYER-WISE PROFIT
            DB::table('fuel_layer_consumptions')->insert([
                'layer_id' => $layer->id,
                'sale_id' => $saleId,
                'qty' => $used,
                'cost_rate' => $layer->rate,
                'sale_rate' => $saleRate,
                'cost_amount' => $cost,
                'sale_amount' => $sale,
                'profit' => $profit,
                'created_at' => now()
            ]);

            // UPDATE LAYER
            DB::table('fuel_inventory_layers')
                ->where('id', $layer->id)
                ->update([
                    'remaining_qty' => $layer->remaining_qty - $used
                ]);

            $remainingQty -= $used;
        }

        if ($remainingQty > 0) {
            DB::rollBack();
            return response()->json(['message' => 'Not enough stock in FIFO layers'], 400);
        }

        // ==============================
        // UPDATE SALE WITH FINAL COST & PROFIT
        // ==============================
        $revenue = $qty * $saleRate;
        $finalProfit = $revenue - $totalCost;

        DB::table('shift_nozzle_readings')
            ->where('id', $saleId)
            ->update([
                'cost_amount' => $totalCost,
                'profit' => $finalProfit
            ]);

        // ==============================
        // UPDATE NOZZLE
        // ==============================
        if (!empty($validatedData['closing_reading'])) {
            DB::table('nozzles')
                ->where('id', $validatedData['nozzle_id'])
                ->update([
                    'intial_meter_reading' => $validatedData['closing_reading'],
                    'updated_at' => now()
                ]);
        }

        // ==============================
        // UPDATE TANK LEVEL
        // ==============================
      //  DB::table('tanks')
        //    ->where('id', $tankId)
          //  ->decrement('current_level', $qty);

        DB::commit();

        return response()->json([
            'message' => 'Sale recorded with FIFO & layer-wise profit',
            'data' => [
                'qty' => $qty,
                'revenue' => $revenue,
                'cost' => $totalCost,
                'profit' => $finalProfit
            ]
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => 'Error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}
    // Update an existing shift nozzle reading
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'opening_reading' => 'nullable|numeric',
            'closing_reading' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE shift_nozzle_readings SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Shift nozzle reading updated successfully']);
    }

    // Delete a shift nozzle reading
    public function destroy($id)
    {
        $deleted = DB::delete('DELETE FROM shift_nozzle_readings WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'Shift nozzle reading deleted successfully']);
        }

        return response()->json(['message' => 'Shift nozzle reading not found'], 404);
    }

    // // Add this method in your ShiftNozzleReadingController
    // public function getLastReading($nozzleId)
    // {
    //     try {
    //         $lastReading = DB::table('shift_nozzle_readings')
    //             ->where('nozzle_id', $nozzleId)
    //             ->orderBy('created_at', 'desc')
    //             ->first();

    //         if ($lastReading) {
    //             return response()->json([
    //                 'success' => true,
    //                 'data' => [
    //                     'closing_reading' => $lastReading->closing_reading,
    //                     'reading_date' => $lastReading->created_at
    //                 ]
    //             ]);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => null,
    //             'message' => 'No previous reading found'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch last reading',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }



    public function getLastReading($nozzleId)
    {
        try {
            // Combine all three sources using UNION and get the latest record
            $latestReading = DB::table(DB::raw('(
            SELECT closing_reading as reading, created_at, "shift_reading" as source 
            FROM shift_nozzle_readings 
            WHERE nozzle_id = ?
            UNION ALL
            SELECT new_reading as reading, created_at, "nozzle_reset" as source 
            FROM nozzle_totalizer_resets 
            WHERE nozzle_id = ?
            UNION ALL
            SELECT intial_meter_reading as reading, created_at, "initial_reading" as source 
            FROM nozzles 
            WHERE id = ?
        ) as combined_readings'))
                ->setBindings([$nozzleId, $nozzleId, $nozzleId])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestReading) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'last_reading' => $latestReading->reading,
                        'source' => $latestReading->source,
                        'reading_date' => $latestReading->created_at
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No previous reading found'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch last reading',
                'error' => $e->getMessage()
            ], 500);
        }
    }
	public function getByShiftAndNozzle($shiftId, $nozzleId)
{
    $readings = DB::select("SELECT * FROM shift_nozzle_readings WHERE shift_id = ? AND nozzle_id = ?", [$shiftId, $nozzleId]);
    return response()->json($readings);
}
}