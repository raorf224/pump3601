<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPricesController extends Controller
{
    // Get all product prices
    public function index()
    {
        $prices = DB::select('SELECT * FROM product_prices');
        return response()->json($prices);
    }

    // Get a single product price by ID
    public function show($id)
    {
        $price = DB::select('SELECT * FROM product_prices WHERE id = ?', [$id]);

        if (empty($price)) {
            return response()->json(['message' => 'Product price not found'], 404);
        }

        return response()->json($price[0]);
    }

    // Create a new product price
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'station_product_id' => 'required|integer',
            'price' => 'required|numeric',
            'effective_from' => 'required|date',
        ]);

        DB::insert(
            'INSERT INTO product_prices (station_product_id, price, effective_from, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())',
            [
                $validatedData['station_product_id'],
                $validatedData['price'],
                $validatedData['effective_from'],
            ]
        );

        return response()->json(['message' => 'Product price created successfully'], 201);
    }

    // Update an existing product price
    public function update(Request $request, $id)
    {
        $price = DB::select('SELECT * FROM product_prices WHERE id = ?', [$id]);

        if (empty($price)) {
            return response()->json(['message' => 'Product price not found'], 404);
        }

        $validatedData = $request->validate([
            'station_product_id' => 'sometimes|required|integer',
            'price' => 'sometimes|required|numeric',
            'effective_from' => 'sometimes|required|date',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE product_prices SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Product price updated successfully']);
    }

    // Delete a product price
    public function destroy($id)
    {
        $price = DB::select('SELECT * FROM product_prices WHERE id = ?', [$id]);

        if (empty($price)) {
            return response()->json(['message' => 'Product price not found'], 404);
        }

        DB::delete('DELETE FROM product_prices WHERE id = ?', [$id]);

        return response()->json(['message' => 'Product price deleted successfully']);
    }
}