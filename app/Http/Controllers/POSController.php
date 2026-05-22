<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PosOrder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class POSController extends Controller
{
    /**
     * Show POS main view (Blade)
     */
    public function index()
    {
        return view('store.pos'); // your Blade file
    }

    /**
     * ✅ Create a new POS Order
     */
public function createOrder(Request $request)
{
    try {
        $validated = $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'category_id' => 'required|string',
            'product_id' => 'required|string',
            'price' => 'required|string',
            'quantity' => 'required|string',
            'total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,card,pending',
            'status' => 'nullable|in:draft,completed,cancelled'
        ]);

        $validated['date'] = Carbon::now()->toDateString();
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['tax'] = $validated['tax'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'completed';

        // ✅ Generate sequential order number
        $lastOrder = PosOrder::orderBy('id', 'desc')->first();
        $nextNumber = $lastOrder ? ((int) str_replace('ORD-', '', $lastOrder->order_id)) + 1 : 1;
        $order_id = 'ORD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Split comma-separated lists
        $categories = explode(',', $validated['category_id']);
        $products = explode(',', $validated['product_id']);
        $prices = explode(',', $validated['price']);
        $quantities = explode(',', $validated['quantity']);

        $count = count($products);
        $insertedOrders = [];

        DB::beginTransaction();

        for ($i = 0; $i < $count; $i++) {
            $productId = (int) ($products[$i] ?? 0);
            $qtyOrdered = (int) ($quantities[$i] ?? 1);

            // ✅ Step 1: Try to fetch product from store inventory
            $product = DB::table('store_products')
                ->where('store_id', $validated['store_id'])
                ->where('id', $productId)
                ->lockForUpdate()
                ->first();

            // ✅ Step 2: Create POS Order regardless of stock status
            $item = [
                'store_id' => $validated['store_id'],
                'order_id' => $order_id,
                'category_id' => $categories[$i] ?? null,
                'product_id' => $productId,
                'price' => $prices[$i] ?? 0,
                'quantity' => $qtyOrdered,
                'discount' => $validated['discount'],
                'tax' => $validated['tax'],
                'payment_type' => $validated['payment_type'],
                'status' => $validated['status'],
                'total' => $validated['total'],
                'date' => $validated['date'],
                'created_at' => now(),
                'updated_at' => now()
            ];

            $insertedOrders[] = PosOrder::create($item);

            // ✅ Step 3: Only subtract stock if product exists AND has quantity > 0
            if ($product && (int)$product->quantity > 0) {
                $qtyToSubtract = min((int)$product->quantity, $qtyOrdered);
                DB::table('store_products')
                    ->where('id', $productId)
                    ->decrement('quantity', $qtyToSubtract);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully. Stock updated where applicable.',
            'order_id' => $order_id,
            'data' => $insertedOrders
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}


    /**
     * ✅ Get all POS orders with category & product names
     */
    public function getOrders()
    {
        try {
            $orders = DB::table('pos_orders as po')
                ->leftJoin('store_products as p', 'po.product_id', '=', 'p.id')
                ->leftJoin('categories as c', 'po.category_id', '=', 'c.id')
                ->leftJoin('stores as s', 'po.store_id', '=', 's.id')
                ->select(
                    'po.*',
                    'p.product_name as product_name',
                    'c.name as category_name',
                    's.store_name as store_name'
                )
                ->orderBy('po.id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

public function getOrders1($user_id)
{
    try {
        // Get user role
        $user = DB::table('users')->where('id', $user_id)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        $role = $user->role;
        
        if ($role === 'admin') {
            // Admin sees all orders
            $orders = DB::table('pos_orders as po')
                ->leftJoin('store_products as p', 'po.product_id', '=', 'p.id')
                ->leftJoin('categories as c', 'po.category_id', '=', 'c.id')
                ->leftJoin('stores as s', 'po.store_id', '=', 's.id')
                ->leftJoin('stations as st', 's.station_id', '=', 'st.id')
                ->select(
                    'po.order_id',
                    DB::raw('GROUP_CONCAT(DISTINCT p.product_name SEPARATOR ", ") as product_names'),
                    DB::raw('GROUP_CONCAT(DISTINCT c.name SEPARATOR ", ") as category_names'),
                    DB::raw('GROUP_CONCAT(DISTINCT po.price SEPARATOR ", ") as product_prices'),
                    DB::raw('SUM(po.quantity) as total_quantity'),
                    DB::raw('SUM(po.price * po.quantity) as total_amount'),
                    DB::raw('MAX(po.date) as date'),
                    DB::raw('MAX(po.payment_type) as payment_type'),
                    DB::raw('MAX(po.status) as status'),
                    DB::raw('MAX(s.store_name) as store_name'),
                    DB::raw('MAX(st.name) as station_name'),
                    DB::raw('MAX(po.id) as latest_id')
                )
                ->groupBy('po.order_id')
                ->orderByDesc('latest_id')
                ->get();
        } 
        elseif ($role === 'owner') {
            // Owner sees orders from their stations
            $orders = DB::table('pos_orders as po')
                ->leftJoin('store_products as p', 'po.product_id', '=', 'p.id')
                ->leftJoin('categories as c', 'po.category_id', '=', 'c.id')
                ->leftJoin('stores as s', 'po.store_id', '=', 's.id')
                ->leftJoin('stations as st', 's.station_id', '=', 'st.id')
                ->where('st.user_id', $user_id)
                ->select(
                    'po.order_id',
                    DB::raw('GROUP_CONCAT(DISTINCT p.product_name SEPARATOR ", ") as product_names'),
                    DB::raw('GROUP_CONCAT(DISTINCT c.name SEPARATOR ", ") as category_names'),
                    DB::raw('GROUP_CONCAT(DISTINCT po.price SEPARATOR ", ") as product_prices'),
                    DB::raw('SUM(po.quantity) as total_quantity'),
                    DB::raw('SUM(po.price * po.quantity) as total_amount'),
                    DB::raw('MAX(po.date) as date'),
                    DB::raw('MAX(po.payment_type) as payment_type'),
                    DB::raw('MAX(po.status) as status'),
                    DB::raw('MAX(s.store_name) as store_name'),
                    DB::raw('MAX(st.name) as station_name'),
                    DB::raw('MAX(po.id) as latest_id')
                )
                ->groupBy('po.order_id')
                ->orderByDesc('latest_id')
                ->get();
        } 
        else {
            // Employee - get orders from their assigned station
            $orders = DB::table('pos_orders as po')
                ->leftJoin('store_products as p', 'po.product_id', '=', 'p.id')
                ->leftJoin('categories as c', 'po.category_id', '=', 'c.id')
                ->leftJoin('stores as s', 'po.store_id', '=', 's.id')
                ->leftJoin('stations as st', 's.station_id', '=', 'st.id')
                ->whereIn('st.id', function($query) use ($user_id) {
                    $query->select('station_id')
                          ->from('employees')
                          ->where('user_id', $user_id);
                })
                ->select(
                    'po.order_id',
                    DB::raw('GROUP_CONCAT(DISTINCT p.product_name SEPARATOR ", ") as product_names'),
                    DB::raw('GROUP_CONCAT(DISTINCT c.name SEPARATOR ", ") as category_names'),
                    DB::raw('GROUP_CONCAT(DISTINCT po.price SEPARATOR ", ") as product_prices'),
                    DB::raw('SUM(po.quantity) as total_quantity'),
                    DB::raw('SUM(po.price * po.quantity) as total_amount'),
                    DB::raw('MAX(po.date) as date'),
                    DB::raw('MAX(po.payment_type) as payment_type'),
                    DB::raw('MAX(po.status) as status'),
                    DB::raw('MAX(s.store_name) as store_name'),
                    DB::raw('MAX(st.name) as station_name'),
                    DB::raw('MAX(po.id) as latest_id')
                )
                ->groupBy('po.order_id')
                ->orderByDesc('latest_id')
                ->get();
        }

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found for this user.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
