<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispensersController extends Controller
{
    // Get dispensers dealer-wise with tank names
    public function getDealerDispensers($dealerId)
    {
        $dispensers = DB::select(
            'SELECT d.id AS dispenser_id, d.name AS dispenser_name, d.status AS dispenser_status, 
                    t.name AS tank_name, s.name AS station_name
             FROM dispensers d
             LEFT JOIN tank_dispenser td ON COALESCE(d.stationrow_id,d.id)  = td.dispenser_id
             LEFT JOIN tanks t ON td.tank_id =  COALESCE(t.stationrow_id,t.id)
             LEFT JOIN stations s ON d.station_id = s.id
             WHERE s.user_id = ?
             ORDER BY d.name ASC',
            [$dealerId]
        );

        if (empty($dispensers)) {
            return response()->json(['message' => 'No dispensers found for this dealer'], 404);
        }

        return response()->json($dispensers);
    }

    // Get all dispensers with tank names
    public function index()
    {
        $dispensers = DB::select(
            'SELECT d.id AS dispenser_id,t.id as tank_id,s.id as station_id, d.name AS dispenser_name, d.status AS dispenser_status, 
                    t.name AS tank_name, s.name AS station_name
             FROM dispensers d
             LEFT JOIN tank_dispenser td ON d.stationrow_id = td.dispenser_id OR d.id = td.dispenser_id
             LEFT JOIN tanks t ON td.tank_id = t.stationrow_id OR td.tank_id = t.id
             LEFT JOIN stations s ON d.station_id = s.id
             ORDER BY d.name ASC'
        );

        return response()->json($dispensers);
    }

    public function index1($user_id)
    {
        $dispensers = DB::select(
            'SELECT d.id AS dispenser_id,t.id as tank_id,s.id as station_id, d.name AS dispenser_name, d.status AS dispenser_status, 
                    t.name AS tank_name, s.name AS station_name, s.user_id as station_user_id
             FROM dispensers d
             LEFT JOIN tank_dispenser td ON d.stationrow_id = td.dispenser_id OR d.id = td.dispenser_id
             LEFT JOIN tanks t ON td.tank_id = t.stationrow_id OR td.tank_id = t.id
             LEFT JOIN stations s ON d.station_id = s.id
             WHERE s.user_id = ?
             ORDER BY d.name ASC
             ',
            [$user_id]
        );

        return response()->json($dispensers);
    }


    // Get a single dispenser by ID
    public function show($id)
    {
        $dispenser = DB::select(
            'SELECT d.*, td.tank_id, t.id as tankk_id, t.name as tank_name, s.id as station_id, s.name as station_name FROM dispensers d
             LEFT JOIN stations s ON d.station_id = s.id
               LEFT JOIN tank_dispenser td ON d.stationrow_id = td.dispenser_id OR d.id = td.dispenser_id
             LEFT JOIN tanks t ON td.tank_id = t.stationrow_id OR td.tank_id = t.id
             WHERE d.id = ?',
            [$id]
        );

        if (empty($dispenser)) {
            return response()->json(['message' => 'Dispenser not found'], 404);
        }

        return response()->json($dispenser[0]);
    }
    public function station_dispensers($id)
    {
		$stationrec=DB::select('select * from stations where id=?',[$id]);
		
		if($stationrec[0]->local=="1"){
			 $dispenser = DB::select(
            'SELECT d.*,t.capacity,t.name as tank_name,t.current_level,t.status as tank_status , s.id as station_id , s.name as station_name FROM tanks t 
            JOIN tank_dispenser td on  t.stationrow_id = td.tank_id
             join dispensers d on  td.dispenser_id = d.stationrow_id
              JOIN stations s on t.station_id = s.id where t.station_id = ?',
            [$id]
        );
		}else{
			$dispenser = DB::select(
            'SELECT d.*,t.capacity,t.name as tank_name,t.current_level,t.status as tank_status , s.id as station_id , s.name as station_name FROM tanks t 
            JOIN tank_dispenser td on t.id = td.tank_id OR t.id = td.tank_id
             join dispensers d on td.dispenser_id =d.id  OR td.dispenser_id = d.id
              JOIN stations s on t.station_id = s.id where t.station_id = ?',
            [$id]
        );
		}
		
        

        if (empty($dispenser)) {
            return response()->json(['message' => 'Dispenser not found'], 404);
        }

        return response()->json($dispenser);
    }



///
// Create a new dispenser with nozzles
public function store(Request $request)
{
    $validatedData = $request->validate([
        'station_id' => 'required|integer',
        'name' => 'required|string|max:50',
        'number_of_nozzels' => 'required|integer|min:1|max:8',
        'intial_date' => 'required|date',
        'status' => 'nullable|in:active,inactive',
        'tank_id' => 'required|integer', // ✅ Main form ka tank (compatibility ke liye)
        'nozzles' => 'required|array',
        'nozzles.*.name' => 'required|string|max:50',
        'nozzles.*.product_id' => 'required|integer',
        'nozzles.*.tank_id' => 'required|integer', // ✅ Nozzle level tanks
        'nozzles.*.intial_date' => 'required|date',
        'nozzles.*.intial_meter_reading' => 'required|numeric', // ✅ ADDED THIS VALIDATION
    ]);

    DB::beginTransaction();

    try {
        // Insert into dispensers table
        DB::insert(
            'INSERT INTO dispensers (station_id, name, number_of_nozzels, intial_date, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
            [
                $validatedData['station_id'],
                $validatedData['name'],
                $validatedData['number_of_nozzels'],
                $validatedData['intial_date'],
                $validatedData['status'] ?? 'active',
            ]
        );

        $dispenserId = DB::getPdo()->lastInsertId();

     
        //     DB::table('synclog')->insert([
        //         'table_name' => 'dispensers',
        //         'record_id'  => $dispenserId,
        //         'action'     => 'insert',
        //         'data'       => json_encode($validatedData),
        //         'created_at' => now()
        //     ]);
        // ✅ FIXED: Insert ALL UNIQUE TANKS in tank_dispenser table
        $uniqueTankIds = [];
        foreach ($validatedData['nozzles'] as $nozzleData) {
            $tankId = $nozzleData['tank_id'];
            
            // Insert only unique tanks in tank_dispenser
            if (!in_array($tankId, $uniqueTankIds)) {
                // $tank_dispenserdata=['tank_id'=>$tankId,"dispenser_id"=>$dispenser_Id];
                DB::insert(
                    'INSERT INTO tank_dispenser (tank_id, dispenser_id, created_at) VALUES (?, ?, NOW())',
                    [$tankId, $dispenserId]
                );
            //     $tank_dispenserId = DB::getPdo()->lastInsertId();

            //      DB::table('synclog')->insert([
            //     'table_name' => 'tank_dispenser',
            //     'record_id'  => $tank_dispenserId,
            //     'action'     => 'insert',
            //     'data'       => json_encode($tank_dispenserdata),
            //     'created_at' => now()
            // ]);
                $uniqueTankIds[] = $tankId;
            }

            // $nozzlesdata=['tank_id'=>$tankId,"dispenser_id"=>$dispenser_Id,"product_id"=>$nozzleData['product_id'],"name"=>$nozzleData['name'],"status"=>"1","intial_date"=>$nozzleData['intial_date'],"intial_meter_reading"=>$nozzleData['intial_meter_reading'],"created_at"=>now()];

            // Insert nozzle WITH initial_meter_reading
            DB::insert(
                'INSERT INTO nozzles (dispenser_id, name, product_id, tank_id, status, intial_date, intial_meter_reading, created_at) VALUES (?, ?, ?, ?, 1, ?, ?, NOW())', // ✅ UPDATED
                [
                    $dispenserId,
                    $nozzleData['name'],
                    $nozzleData['product_id'],
                    $tankId,
                    $nozzleData['intial_date'],
                    $nozzleData['intial_meter_reading'], // ✅ ADDED
                ]
            );
            // $nozzlesId = DB::getPdo()->lastInsertId();

            //      DB::table('synclog')->insert([
            //     'table_name' => 'tank_dispenser',
            //     'record_id'  => $nozzlesId,
            //     'action'     => 'insert',
            //     'data'       => json_encode($nozzlesdata),
            //     'created_at' => now()
            // ]);
        }

        DB::commit();
        
        // ✅ FIXED: Return proper JSON response
        return response()->json([
            'message' => 'Dispenser created successfully',
            'success' => true
        ], 201);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        // ✅ FIXED: Return proper JSON error response
        return response()->json([
            'message' => 'Failed to create dispenser', 
            'error' => $e->getMessage(),
            'success' => false
        ], 500);
    }
}

    // Update an existing dispenser
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:50',
            'status' => 'sometimes|required|in:active,inactive',
            'tank_id' => 'sometimes|required|integer',
        ]);

        DB::beginTransaction();

        try {
            // Update dispensers table
            $updateFields = [];
            $updateValues = [];

            if (isset($validatedData['name'])) {
                $updateFields[] = 'name = ?';
                $updateValues[] = $validatedData['name'];
            }

            if (isset($validatedData['status'])) {
                $updateFields[] = 'status = ?';
                $updateValues[] = $validatedData['status'];
            }

            $updateValues[] = $id;

            if (!empty($updateFields)) {
                DB::update(
                    'UPDATE dispensers SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?',
                    $updateValues
                );
            //     DB::table('synclog')->insert([
            //     'table_name' => 'dispensers',
            //     'record_id'  => $id,
            //     'action'     => 'update',
            //     'data'       => json_encode($validatedData),
            //     'created_at' => now()
            // ]);
            }

            // Update tank_dispenser table if tank_id is provided
            if (isset($validatedData['tank_id'])) {
                DB::update(
                    'UPDATE tank_dispenser SET tank_id = ? WHERE dispenser_id = ?',
                    [$validatedData['tank_id'], $id]
                );
                $tank_dispenser= DB::select("select * from tank_dispenser where tank_id=? and dispenser_id=?",[$validatedData['tank_id'],$id]);
            //     $tankdata=["tank_id"=>$validatedData['tank_id'],"dispenser_id"=>$id];
            //     DB::table('synclog')->insert([
            //     'table_name' => 'tank_dispenser',
            //     'record_id'  => $tank_dispenser[0]->id,
            //     'action'     => 'update',
            //     'data'       => json_encode($tankdata),
            //     'created_at' => now()
            // ]);
            }

            DB::commit();

            return response()->json(['message' => 'Dispenser updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update dispenser', 'error' => $e->getMessage()], 500);
        }
    }

    // Delete a dispenser
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Delete from tank_dispenser table
            DB::delete('DELETE FROM tank_dispenser WHERE dispenser_id = ?', [$id]);

            // Delete from dispensers table
            $deleted = DB::delete('DELETE FROM dispensers WHERE id = ?', [$id]);
            // DB::insert("INSERT INTO `synclog`( `table_name`, `record_id`, `action`) VALUES ('tank_dispenser',?,'delete')",[$id,$data]);

            DB::commit();

            if ($deleted) {
                return response()->json(['message' => 'Dispenser deleted successfully']);
            }

            return response()->json(['message' => 'Dispenser not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete dispenser', 'error' => $e->getMessage()], 500);
        }
    }
}