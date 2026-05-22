<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NozzleTotalizerResetController extends Controller
{
    // Get all resets
    public function index()
    {
        $resets = DB::select(
            'SELECT r.id, r.old_reading, r.new_reading, r.reason, r.reset_date,r.created_by,
                    n.id as nozzle_id, n.name as nozzle_name,
                    d.id as dispenser_id, d.name as dispenser_name,
                    st.id as station_id, st.name as station_name,
                    u.id as user_id, u.full_name as username
             FROM nozzle_totalizer_resets r
             LEFT JOIN nozzles n ON r.nozzle_id = n.id OR r.nozzle_id = n.stationrow_id 
             LEFT JOIN dispensers d ON n.dispenser_id = d.id OR n.dispenser_id = d.stationrow_id
             LEFT join users u on r.created_by = u.id OR r.created_by = u.stationrow_id
             LEFT JOIN stations st ON d.station_id = st.id
             ORDER BY r.reset_date DESC'
        );

        return response()->json($resets);
    }

    // ✅ Get resets by station_id
    public function show($stationId)
    {
        $stationrec =DB::select("select * from stations where id =?",[$stationId]);

        if($stationrec[0]->local=="1"){
            $resets = DB::select(
            'SELECT r.id, r.old_reading, r.new_reading, r.reason, r.reset_date,r.created_by,
                    n.id as nozzle_id, n.name as nozzle_name,
                    d.id as dispenser_id, d.name as dispenser_name,
                    st.id as station_id, st.name as station_name,
                    u.id as user_id, u.full_name as username
             FROM nozzle_totalizer_resets r
             LEFT JOIN nozzles n ON r.nozzle_id = n.stationrow_id
             LEFT JOIN dispensers d ON n.dispenser_id = d.stationrow_id
             LEFT JOIN users u on r.created_by = u.stationrow_id
             LEFT JOIN stations st ON d.station_id = st.id
             WHERE st.id = ?
             ORDER BY r.reset_date DESC',
            [$stationId]
        );
        }else{

        
        $resets = DB::select(
            'SELECT r.id, r.old_reading, r.new_reading, r.reason, r.reset_date,r.created_by,
                    n.id as nozzle_id, n.name as nozzle_name,
                    d.id as dispenser_id, d.name as dispenser_name,
                    st.id as station_id, st.name as station_name,
                    u.id as user_id, u.full_name as username
             FROM nozzle_totalizer_resets r
             LEFT JOIN nozzles n ON r.nozzle_id = n.id
             LEFT JOIN dispensers d ON n.dispenser_id = d.id
             LEFT JOIN users u on r.created_by = u.id
             LEFT JOIN stations st ON d.station_id = st.id
             WHERE st.id = ?
             ORDER BY r.reset_date DESC',
            [$stationId]
        );
        }

        return response()->json($resets);
    }

    // Get resets by nozzle
    public function getByNozzle($nozzleId)
    {
        $resets = DB::select(
            'SELECT r.id, r.old_reading, r.new_reading, r.reason, r.reset_date
             FROM nozzle_totalizer_resets r
             WHERE r.nozzle_id = ?
             ORDER BY r.reset_date DESC',
            [$nozzleId]
        );

        return response()->json($resets);
    }

    // Store a new reset record
public function store(Request $request)
{
    $validated = $request->validate([
        'nozzle_id' => 'required|integer|exists:nozzles,id',
        'shift_id' => 'required|integer|exists:shifts,id',
        'reset_date' => 'required|date', // ✅ ADDED
        'old_reading' => 'required|numeric',
        'new_reading' => 'required|numeric',
        'total_dispensed' => 'required|numeric',
        'rate' => 'required|numeric',
        'total_amount' => 'required|numeric',
        'reason' => 'nullable|string',
        'created_by' => 'nullable|integer|exists:users,id',
    ]);

    // ✅ START TRANSACTION
    DB::beginTransaction();

    try {
        // 1. Insert reset record with reset_date from user
        DB::insert(
            'INSERT INTO nozzle_totalizer_resets (nozzle_id, shift_id, reset_date, old_reading, new_reading, 
             total_dispensed, rate, total_amount, reason, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $validated['nozzle_id'],
                $validated['shift_id'],
                $validated['reset_date'], // ✅ Use user provided date
                $validated['old_reading'],
                $validated['new_reading'],
                $validated['total_dispensed'],
                $validated['rate'],
                $validated['total_amount'],
                $validated['reason'] ?? null,
                $validated['created_by'] ?? null,
            ]
        );

        // 2. Update nozzle initial meter reading
        DB::table('nozzles')
            ->where('id', $validated['nozzle_id'])
            ->update([
                'intial_meter_reading' => $validated['new_reading'],
                'intial_date' => $validated['reset_date'], // ✅ Use user provided date
                'updated_at' => now(),
            ]);

        // ✅ COMMIT TRANSACTION
        DB::commit();

        return response()->json([
            'message' => 'Nozzle totalizer reset recorded and nozzle reading updated successfully'
        ], 201);

    } catch (\Exception $e) {
        // ✅ ROLLBACK TRANSACTION
        DB::rollBack();

        return response()->json([
            'message' => 'Error saving nozzle reset: ' . $e->getMessage()
        ], 500);
    }
}

public function getPriceByDate($stationId, $productId, $date)
{
    try {
        // Convert ISO date to MySQL datetime format
        $dateTime = date('Y-m-d H:i:s', strtotime($date));
        
        // Debug info
        \Log::info("Fetching price for station: {$stationId}, product: {$productId}, date: {$dateTime}");
        $stationrec =DB::select("select * from stations where id =?",[$stationId]);
       if($stationrec[0]->local=="1"){
        // ✅ DIRECT QUERY - nozzles table mein directly product_id hai
        // Pehle check karo ki station pe yeh product available hai
        $stationProduct = DB::table('nozzles')
            ->join('dispensers', 'nozzles.dispenser_id', '=', 'dispensers.stationrow_id')
            ->where('dispensers.station_id', $stationId)
            ->where('nozzles.product_id', $productId)
            ->first();
       }else{
        $stationProduct = DB::table('nozzles')
            ->join('dispensers', 'nozzles.dispenser_id', '=', 'dispensers.id')
            ->where('dispensers.station_id', $stationId)
            ->where('nozzles.product_id', $productId)
            ->first();
       }
        if (!$stationProduct) {
            return response()->json([
                'message' => 'Product not found at this station'
            ], 404);
        }

        // ✅ Ab directly product_prices table mein query karo
        // Pehle station_product_id find karo station_products table se
        $stationProductRecord = DB::table('station_products')
            ->where('station_id', $stationId)
            ->where('product_id', $productId)
            ->first();

        if (!$stationProductRecord) {
            return response()->json([
                'message' => 'Station product mapping not found'
            ], 404);
        }

        $stationProductId = $stationProductRecord->id;

        // Get effective price for the given date
        $productPrice = DB::table('product_prices')
            ->where('station_product_id', $stationProductId)
            ->where('effective_from', '<=', $dateTime)
            ->where(function($query) use ($dateTime) {
                $query->where('effective_to', '>=', $dateTime)
                      ->orWhereNull('effective_to');
            })
            ->orderBy('effective_from', 'desc')
            ->first();

        // If no price found with effective_to condition, try getting latest active price
        if (!$productPrice) {
            $productPrice = DB::table('product_prices')
                ->where('station_product_id', $stationProductId)
                ->where('effective_from', '<=', $dateTime)
                ->orderBy('effective_from', 'desc')
                ->first();
        }

        if (!$productPrice) {
            return response()->json([
                'message' => 'Product price not found for the given date'
            ], 404);
        }

        return response()->json([
            'price' => (float)$productPrice->price,
            'effective_from' => $productPrice->effective_from,
            'effective_to' => $productPrice->effective_to,
            'station_product_id' => $stationProductId
        ]);

    } catch (\Exception $e) {
        \Log::error("Error fetching product price: " . $e->getMessage());
        return response()->json([
            'message' => 'Error fetching product price',
            'error' => $e->getMessage()
        ], 500);
    }
}
}