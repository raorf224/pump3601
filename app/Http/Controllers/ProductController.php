<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // CREATE product
    public function store(Request $request)
    {
        DB::insert('INSERT INTO products (name, category, description, unit) VALUES (?, ?, ?, ?)', [
            $request->name,
            $request->category,
            $request->description,
            $request->unit,

        ]);

        return response()->json(['message' => 'Product created successfully'], 201);
    }

    // GET all products
    public function index()
    {
        $products = DB::select('SELECT * FROM products');

        if (!$products) {
            return response()->json(['message' => 'No products found'], 404);
        }

        return response()->json($products, 200);
    }

    public function getByCategory($category)
    {
        // Validate category input
        if (!in_array($category, ['fuel', 'lubricants'])) {
            return response()->json(['message' => 'Invalid category'], 400);
        }

        // Fetch products by category
        $products = DB::select('SELECT * FROM products WHERE category = ?', [$category]);

        if (empty($products)) {
            return response()->json(['message' => 'No products found for this category'], 404);
        }

        return response()->json($products, 200);
    }



    // GET single product by id
    public function station_products($id)
    {
        $product = DB::select('SELECT p.*,sp.stock,sp.id as spid,sp.station_id,sp.product_id FROM `station_products` sp join products p on sp.product_id =p.id where sp.station_id =?', [$id]);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }

    public function show($id)
    {
        $product = DB::select('SELECT * FROM products WHERE id = ?', [$id]);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product[0], 200);
    }

    // UPDATE product
    public function update(Request $request, $id)
    {
        $affected = DB::update('UPDATE products SET name = ?, category = ?, description = ?, unit = ? WHERE id = ?', [
            $request->name,
            $request->category,
            $request->description,
            $request->unit,
            $id
        ]);

        if ($affected) {
            return response()->json(['message' => 'Product updated successfully'], 200);
        } else {
            return response()->json(['message' => 'Product not found or not updated'], 404);
        }
    }

    // DELETE product
    public function destroy($id)
    {
        $deleted = DB::delete('DELETE FROM products WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'Product deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }
    public function stationproduct(Request $request)
    {

        DB::insert('INSERT INTO `station_products`( `station_id`, `product_id`, `stock`) VALUES (?,?,?)', [$request->station_id, $request->product_id, $request->stock]);
        $inserted_id = getPDO()->lastinsertedId;

    }

public function getStationProduct($id)
{
    $product = DB::select("
        SELECT sp.id as station_product_id, 
               sp.station_id,
               s.name as station_name,
               p.id as product_id,
               p.name as product_name,
               sp.stock,
               pp.price,
               pp.effective_from,
               pp.effective_to
        FROM station_products sp
        JOIN products p ON sp.product_id = p.id
        LEFT JOIN stations s on sp.station_id = s.id
        LEFT JOIN product_prices pp ON pp.station_product_id = sp.id
        WHERE sp.id = ?
        ORDER BY pp.created_at DESC, pp.id DESC  -- ✅ Yahan change kiya
        LIMIT 1
    ", [$id]);

    if (!$product) {
        return response()->json(['message' => 'Station Product not found'], 404);
    }

    return response()->json($product[0], 200);
}

public function updateStationProduct(Request $request, $id)
{
    DB::beginTransaction();
    try {
        // 1. Update stock in station_products
        DB::update("
            UPDATE station_products 
            SET stock = ?, updated_at = NOW() 
            WHERE id = ?
        ", [
            $request->stock,
            $id
        ]);

        // 2. ✅ FIXED: INSERT NEW PRICE RECORD (update nahi)
        DB::insert("
            INSERT INTO product_prices (station_product_id, price, effective_from, effective_to, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ", [
            $id,
            $request->price,
            $request->effective_from,
            $request->effective_to, // ✅ ADDED
        ]);

        DB::commit();
        return response()->json(['message' => 'Station Product updated successfully'], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to update Station Product',
            'error' => $e->getMessage()
        ], 500);
    }
}


// ProductController.php main
public function getStationProducts($stationId)
{
    $products = DB::select(
        'SELECT DISTINCT p.*
         FROM products p
         JOIN tanks t ON p.id = t.product_id
         WHERE t.station_id = ? AND t.status = "active"
         ORDER BY p.name',
        [$stationId]
    );

    return response()->json($products);
}


}
