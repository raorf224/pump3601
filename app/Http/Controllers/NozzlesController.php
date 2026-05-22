<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NozzlesController extends Controller
{
    // Get all nozzles with tank, dispenser, and station names
    public function index()
    {
        $nozzles = DB::select(
            'SELECT 
                n.id AS nozzle_id, 
                n.name AS nozzle_name, 
                n.status AS nozzle_status,
                n.intial_meter_reading,
                t.name AS tank_name, 
                d.name AS dispenser_name, 
                s.name AS station_name,
                COALESCE(
                    (SELECT closing_reading 
                     FROM shift_nozzle_readings 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    (SELECT new_reading 
                     FROM nozzle_totalizer_resets 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    n.intial_meter_reading
                ) as current_reading
             FROM nozzles n
             LEFT JOIN tanks t ON n.tank_id = t.id OR n.tank_id = t.stationrow_id
             LEFT JOIN dispensers d ON n.dispenser_id = d.id OR n.dispenser_id = d.stationrow_id
             LEFT JOIN stations s ON d.station_id = s.id
             ORDER BY n.name ASC'
        );

        return response()->json($nozzles);
    }

    public function index1($user_id)
    {
        $nozzles = DB::select(
            'SELECT 
                n.id AS nozzle_id, 
                n.name AS nozzle_name, 
                n.status AS nozzle_status,
                n.intial_meter_reading,
                t.name AS tank_name, 
                d.name AS dispenser_name, 
                s.name AS station_name,
                COALESCE(
                    (SELECT closing_reading 
                     FROM shift_nozzle_readings 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    (SELECT new_reading 
                     FROM nozzle_totalizer_resets 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    n.intial_meter_reading
                ) as current_reading
             FROM nozzles n
                         LEFT JOIN tanks t ON n.tank_id = t.id OR n.tank_id = t.stationrow_id
             LEFT JOIN dispensers d ON n.dispenser_id = d.id OR n.dispenser_id = d.stationrow_id
             LEFT JOIN stations s ON d.station_id = s.id
             WHERE s.user_id = ?
             ORDER BY n.name ASC',
            [$user_id]
        );

        return response()->json($nozzles);
    }

    // Get a single nozzle by ID
    public function show($id)
    {
        $nozzle = DB::select(
            'SELECT 
                n.id AS nozzle_id, 
                t.id as tank_id,
                d.id as dispenser_id, 
                n.name AS nozzle_name, 
                n.status AS nozzle_status,
                n.intial_meter_reading,
                t.name AS tank_name, 
                d.name AS dispenser_name, 
                s.name AS station_name,
                COALESCE(
                    (SELECT closing_reading 
                     FROM shift_nozzle_readings 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    (SELECT new_reading 
                     FROM nozzle_totalizer_resets 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    n.intial_meter_reading
                ) as current_reading
             FROM nozzles n
            LEFT JOIN tanks t ON n.tank_id = t.id OR n.tank_id = t.stationrow_id
             LEFT JOIN dispensers d ON n.dispenser_id = d.id OR n.dispenser_id = d.stationrow_id
             LEFT JOIN stations s ON d.station_id = s.id
             WHERE n.id = ?',
            [$id]
        );

        if (empty($nozzle)) {
            return response()->json(['message' => 'Nozzle not found'], 404);
        }

        return response()->json($nozzle[0]);
    }

    public function station_nozzle($id)
    {
		$stationrec=DB::select("select * from stations where id=?",[$id]);
		if($stationrec[0]->local=="1"){
			
        $nozzle = DB::select(
            'SELECT 
                n.*, 
                n.status as nozzle_status,
                d.name as dispenser_name, 
                t.current_level as tank_reading,
                COALESCE(
                    (SELECT closing_reading 
                     FROM shift_nozzle_readings 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    (SELECT new_reading 
                     FROM nozzle_totalizer_resets 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    n.intial_meter_reading
                ) as nozzle_reading
             FROM nozzles n 
            JOIN dispensers d on n.dispenser_id = d.stationrow_id
             JOIN tanks t on n.tank_id = t.id  OR n.tank_id = t.stationrow_id
             WHERE t.station_id = ?',
            [$id]
        );
		}else{
					
        $nozzle = DB::select(
            'SELECT 
                n.*, 
                n.status as nozzle_status,
                d.name as dispenser_name, 
                t.current_level as tank_reading,
                COALESCE(
                    (SELECT closing_reading 
                     FROM shift_nozzle_readings 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    (SELECT new_reading 
                     FROM nozzle_totalizer_resets 
                     WHERE nozzle_id = n.id 
                     ORDER BY created_at DESC 
                     LIMIT 1),
                    n.intial_meter_reading
                ) as nozzle_reading
             FROM nozzles n 
            JOIN dispensers d on n.dispenser_id = d.id 
             JOIN tanks t on n.tank_id = t.id  OR n.tank_id = t.id
             WHERE t.station_id = ?',
            [$id]
        );}

        if (empty($nozzle)) {
            return response()->json(['message' => 'Nozzle not found'], 404);
        }

        return response()->json($nozzle);
    }

    // Create a new nozzle
    public function store(Request $request)
    {
        DB::insert(
            'INSERT INTO nozzles (dispenser_id, name, product_id, tank_id, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
            [
                $request['dispenser_id'],
                $request['name'],
                $request['product_id'],
                $request['tank_id'],
                $request['status'] ?? 1,
            ]
        );

        return response()->json(['message' => 'Nozzle created successfully'], 201);
    }

    // Update an existing nozzle
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'dispenser_id' => 'sometimes|required|integer',
            'name' => 'sometimes|required|string|max:50',
            'product_id' => 'sometimes|required|integer',
            'tank_id' => 'sometimes|required|integer',
            'status' => 'sometimes|required|boolean',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE nozzles SET ' . implode(', ', $updateFields) . ' WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Nozzle updated successfully']);
    }

    // Delete a nozzle
    public function destroy($id)
    {
        $deleted = DB::delete('DELETE FROM nozzles WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'Nozzle deleted successfully']);
        }

        return response()->json(['message' => 'Nozzle not found'], 404);
    }

    public function getByStation($stationId)
    {
        $stationrec=DB::select("select * from stations where id =?",[$stationId]);
         if($stationrec[0]->local=="1"){
                $nozzles = DB::table('nozzles as n')
                ->join('dispensers as d', 'n.dispenser_id', '=', 'd.stationrow_id')
                ->leftJoin('tanks as t', 'n.tank_id', '=', 't.stationrow_id')
                
            ->leftJoin('products as p', 'n.product_id', '=', 'p.id')
            ->where('d.station_id', $stationId)
            ->where('n.status', 1)
            ->select(
                'n.id',
                'n.name',
                'n.product_id',
                'n.tank_id',
                'n.intial_meter_reading',
                't.name as tank_name',
                'p.name as product_name',
                'd.name as dispenser_name'
            )
            ->get();
         }else{
        $nozzles = DB::table('nozzles as n')
            ->join('dispensers as d', 'n.dispenser_id', '=', 'd.id')
            ->leftJoin('tanks as t', 'n.tank_id', '=', 't.id')
            ->leftJoin('products as p', 'n.product_id', '=', 'p.id')
            ->where('d.station_id', $stationId)
            ->where('n.status', 1)
            ->select(
                'n.id',
                'n.name',
                'n.product_id',
                'n.tank_id',
                'n.intial_meter_reading',
                't.name as tank_name',
                'p.name as product_name',
                'd.name as dispenser_name'
            )
            ->get();
        }

        return response()->json($nozzles);
    }
}