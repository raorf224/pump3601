<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TanksController extends Controller
{
    // Get all tanks with product name
    public function index()
    {
        $tanks = DB::select('
           SELECT t.*, products.name as product_name, s.id as station_id , s.name as station_name
            FROM tanks t
            LEFT JOIN products ON t.product_id = products.id
            LEFT JOIN stations s on t.station_id = s.id
        ');
        return response()->json($tanks);
    }

    public function index1($user_id)
    {
        $tanks = DB::select('
        SELECT 
            t.*, 
            products.name AS product_name, 
            s.id AS station_id, 
            s.name AS station_name
        FROM tanks t
        LEFT JOIN products ON t.product_id = products.id
        LEFT JOIN stations s ON t.station_id = s.id
        WHERE s.user_id = ?
    ', [$user_id]);

        return response()->json($tanks);
    }


    // Get a single tank by ID with product name
    public function show($id)
    {
        $tank = DB::select('
SELECT t.*, products.name as product_name , s.id as station_id, s.name as station_name FROM tanks t LEFT JOIN stations s on t.station_id = s.id JOIN products ON t.product_id = products.id WHERE t.id = ?;

        ', [$id]);

        if (empty($tank)) {
            return response()->json(['message' => 'Tank not found'], 404);
        }

        return response()->json($tank[0]);
    }

    // Get tanks station-wise with product name
    public function stationwise($id)
    {
        $tank = DB::select('
            SELECT tanks.*, products.name as product_name , s.id as station_id, s.name as station_name
            FROM tanks 
            JOIN products ON tanks.product_id = products.id
            left JOIN stations s on tanks.station_id = s.id
            WHERE tanks.station_id = ?
        ', [$id]);

        if (empty($tank)) {
            return response()->json(['message' => 'Tank not found'], 404);
        }

        return response()->json($tank);
    }

    // Create a new tank
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'station_id' => 'required|integer',
            'product_id' => 'required|integer',
            'name' => 'required|string|max:50',
            'capacity' => 'required|numeric|min:0',
            'current_level' => 'required|numeric|min:0',
            'current_level_mm' => 'required|numeric|min:0',
            'dry_limit' => 'required|numeric|min:0',
            'intial_date' => 'required|date',
            'status' => 'required|in:active,inactive',

            // ✅ NEW
            'initial' => 'nullable|boolean',
            'buying_price' => 'nullable|numeric|min:0'
        ]);

        // ✅ EXTRA VALIDATION
        if ($request->initial && !$request->buying_price) {
            return response()->json([
                'message' => 'Buying price is required for initial setup'
            ], 422);
        }

        if ($request->current_level > $request->capacity) {
            return response()->json([
                'message' => 'Current level cannot exceed capacity'
            ], 422);
        }

        DB::beginTransaction();

        try {

            // ✅ INSERT TANK
            $tankId = DB::table('tanks')->insertGetId([
                'station_id' => $validatedData['station_id'],
                'product_id' => $validatedData['product_id'],
                'name' => $validatedData['name'],
                'capacity' => $validatedData['capacity'],
                'current_level' => $validatedData['current_level'],
                'current_level_mm' => $validatedData['current_level_mm'],
                'dry_limit' => $validatedData['dry_limit'],
                'intial_date' => $validatedData['intial_date'],
                'status' => $validatedData['status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ✅ INSERT INITIAL INVENTORY LAYER
            if (!empty($validatedData['initial']) && $validatedData['initial'] == 1) {

                DB::table('fuel_inventory_layers')->insert([
                    'tank_id' => $tankId,
                    'product_id' => $validatedData['product_id'],
                    'remaining_qty' => $validatedData['current_level'],
                    'rate' => $validatedData['buying_price'],
                    'created_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tank created successfully'
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error creating tank',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update tank
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'station_id' => 'sometimes|required|integer',
            'product_id' => 'sometimes|required|integer',
            'name' => 'sometimes|required|string|max:50',
            'capacity' => 'sometimes|required|numeric|min:0',
            'current_level' => 'sometimes|required|numeric|min:0',
            'current_level_mm' => 'sometimes|required|numeric|min:0',
            'dry_limit' => 'sometimes|required|numeric|min:0',
            'intial_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE tanks SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Tank updated successfully']);
    }


    // Delete a tank
    public function destroy($id)
    {
        $tank = DB::select('SELECT * FROM tanks WHERE id = ?', [$id]);

        if (empty($tank)) {
            return response()->json(['message' => 'Tank not found'], 404);
        }

        DB::delete('DELETE FROM tanks WHERE id = ?', [$id]);

        return response()->json(['message' => 'Tank deleted successfully']);
    }

    public function getByStation($stationId)
    {
        $tanks = DB::table('tanks as t')
            ->leftJoin('products as p', 't.product_id', '=', 'p.id')
            ->where('t.station_id', $stationId)
            ->where('t.status', 'active')
            ->select(
                't.id',
                't.name',
                't.capacity',
                't.current_level',
                't.current_level_mm',
                'p.name as product_name'
            )
            ->get();

        return response()->json($tanks);
    }

    public function getByStationWithShift($stationId, $shiftId)
    {
        $tanks = DB::table('tanks as t')
            ->leftJoin('products as p', 't.product_id', '=', 'p.id')
            ->leftJoin('tanks_dip as td', function ($join) use ($shiftId) {
                $join->on('t.id', '=', 'td.tank_id')
                    ->where('td.shift_id', '=', $shiftId);
            })
            ->where('t.station_id', $stationId)
            ->where('t.status', 'active')
            ->select(
                't.id',
                't.name',
                't.capacity',
                't.current_level',
                't.current_level_mm',
                'p.name as product_name',
                'td.dip_mm as shift_dip_mm',
                'td.dip_in_liters as shift_dip_liters',
                'td.old_dip_mm',
                'td.old_dip_liters'
            )
            ->get();

        return response()->json($tanks);
    }


    public function getStationProductTanks($stationId, $productId)
    {
        $tanks = DB::select(
            'SELECT t.*, p.name as product_name, s.name as station_name
         FROM tanks t
         JOIN products p ON t.product_id = p.id
         JOIN stations s ON t.station_id = s.id
         WHERE t.station_id = ? AND t.product_id = ? 
         AND t.status = "active"
         ORDER BY t.name',
            [$stationId, $productId]
        );

        return response()->json($tanks);
    }
}
