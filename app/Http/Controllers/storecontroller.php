<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class storecontroller extends Controller
{
    /**
     * CREATE store
     */
    public function store(Request $request)
    {
        DB::insert(
            'INSERT INTO stores 
            (station_id, store_name, store_code, owned_by, manager_name, contact_number, status, opening_date, closing_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $request->station_id,
                $request->store_name,
                $request->store_code,
                $request->owned_by,
                $request->manager_name,
                $request->contact_number,
                $request->status ?? 'active',
                $request->opening_date,
                $request->closing_date
            ]
        );

        return response()->json(['message' => 'Store created successfully'], 201);
    }

    /**
     * GET all stores (with station name)
     */
    public function index()
    {
        $stores = DB::select('
            SELECT stores.*, s.name AS station_name, s.location as station_location, s.id as station_id
            FROM stores 
            JOIN stations s ON stores.station_id = s.id
            ORDER BY stores.id DESC
        ');

        if (!$stores) {
            return response()->json(['message' => 'No stores found'], 404);
        }

        return response()->json($stores, 200);
    }

/**
 * GET stores for a specific user
 * For admin: show all stores
 * For owner: show stores from their stations
 * For employee: show stores from their assigned station (based on employees table)
 */
public function index1($user_id)
{
    // Get user role
    $user = DB::select('SELECT role FROM users WHERE id = ?', [$user_id]);
    
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }
    
    $role = $user[0]->role;
    
    if ($role === 'admin') {
        // Admin sees all stores
        $stores = DB::select('
            SELECT stores.*, s.name AS station_name, s.location as station_location, s.id as station_id, s.user_id as station_user_id
            FROM stores 
            JOIN stations s ON stores.station_id = s.id
            ORDER BY stores.id DESC
        ');
    } 
    elseif ($role === 'owner') {
        // Owner sees stores from their stations
        $stores = DB::select('
            SELECT stores.*, s.name AS station_name, s.location as station_location, s.id as station_id, s.user_id as station_user_id
            FROM stores 
            JOIN stations s ON stores.station_id = s.id
            WHERE s.user_id = ?
            ORDER BY stores.id DESC
        ', [$user_id]);
    } 
    else {
        // Employee - get stores from their assigned station
        $stores = DB::select('
            SELECT DISTINCT stores.*, s.name AS station_name, s.location as station_location, s.id as station_id, s.user_id as station_user_id
            FROM stores 
            JOIN stations s ON stores.station_id = s.id
            WHERE s.id IN (
                SELECT station_id FROM employees WHERE user_id = ?
            )
            ORDER BY stores.id DESC
        ', [$user_id]);
    }

    if (empty($stores)) {
        return response()->json(['message' => 'No stores found'], 404);
    }

    return response()->json($stores, 200);
}

    /**
     * GET single store by ID
     */
    public function show($id)
    {
        $store = DB::select('
            SELECT stores.*, s.name AS station_name, s.location as station_location, s.id as station_id
            FROM stores 
            JOIN stations s ON stores.station_id = s.id
            WHERE stores.id = ?
        ', [$id]);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        return response()->json($store[0], 200);
    }

    /**
     * UPDATE store
     */
    public function update(Request $request, $id)
    {
        $affected = DB::update('
            UPDATE stores SET 
            station_id = ?, 
            store_name = ?, 
            store_code = ?, 
            owned_by = ?, 
            manager_name = ?, 
            contact_number = ?, 
            status = ?, 
            opening_date = ?, 
            closing_date = ?
            WHERE id = ?',
            [
                $request->station_id,
                $request->store_name,
                $request->store_code,
                $request->owned_by,
                $request->manager_name,
                $request->contact_number,
                $request->status,
                $request->opening_date,
                $request->closing_date,
                $id
            ]
        );

        if ($affected) {
            return response()->json(['message' => 'Store updated successfully'], 200);
        }

        return response()->json(['message' => 'Store not found'], 404);
    }

    /**
     * DELETE store
     */
    public function destroy($id)
    {
        $deleted = DB::delete('DELETE FROM stores WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'Store deleted successfully'], 200);
        }

        return response()->json(['message' => 'Store not found'], 404);
    }
}