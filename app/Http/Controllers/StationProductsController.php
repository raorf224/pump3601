<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StationProductsController extends Controller
{
// Get station-wise products with their prices
public function getStationProductsWithPrices($stationId)
{
    $products = DB::select(
        'SELECT 
            sp.id AS station_product_id,
            s.name as station,
            p.name as product,
            p.category, 
            sp.station_id, 
            sp.product_id, 
            sp.stock, 
            pp.price, 
            pp.effective_from,
            pp.effective_to
         FROM station_products sp
         LEFT JOIN stations s ON sp.station_id = s.id
         LEFT JOIN products p ON sp.product_id = p.id
         LEFT JOIN product_prices pp ON sp.id = pp.station_product_id
         WHERE sp.station_id = ?
         AND pp.id = (
             SELECT id FROM product_prices pp2 
             WHERE pp2.station_product_id = sp.id 
             ORDER BY pp2.created_at DESC, pp2.id DESC  -- ✅ Yahan change kiya
             LIMIT 1
         )
         ORDER BY p.name',
        [$stationId]
    );

    if (empty($products)) {
        return response()->json(['message' => 'No products found for this station'], 404);
    }

    return response()->json($products);
}

    // Assign a product to a station
    public function assignProductToStation(Request $request)
    {
        $validatedData = $request->validate([
            'station_id' => 'required|integer',
            'product_id' => 'required|integer',
            'stock' => 'nullable|numeric',
        ]);

        DB::insert(
            'INSERT INTO station_products (station_id, product_id, stock, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())',
            [
                $validatedData['station_id'],
                $validatedData['product_id'],
                $validatedData['stock'] ?? 0.00,
            ]
        );

        return response()->json(['message' => 'Product assigned to station successfully'], 201);
    }

// Assign a product to a station with price
public function assignProductWithPrice(Request $request)
{
    $validatedData = $request->validate([
        'station_id' => 'required|integer',
        'product_id' => 'required|integer',
        'stock' => 'nullable|numeric',
        'price' => 'required|numeric',
        'effective_from' => 'required|date',
        'effective_to' => 'required|date', // ✅ ADDED
    ]);

    DB::beginTransaction();

    try {
        // Insert into station_products table
        DB::insert(
            'INSERT INTO station_products (station_id, product_id, stock, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())',
            [
                $validatedData['station_id'],
                $validatedData['product_id'],
                $validatedData['stock'] ?? 0.00,
            ]
        );

        // Get the last inserted station_product_id
        $stationProductId = DB::getPdo()->lastInsertId();

        // Insert into product_prices table WITH effective_to
        DB::insert(
            'INSERT INTO product_prices (station_product_id, price, effective_from, effective_to, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())',
            [
                $stationProductId,
                $validatedData['price'],
                $validatedData['effective_from'],
                $validatedData['effective_to'], // ✅ ADDED
            ]
        );

        DB::commit();

        return response()->json(['message' => 'Product assigned to station with price successfully'], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed to assign product with price', 'error' => $e->getMessage()], 500);
    }
}


}