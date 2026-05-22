<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    // CREATE user
    public function store(Request $request)
    {
        DB::insert('INSERT INTO users 
        (username, email, password, description, full_name, phone, role, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $request->username,
                $request->email,
                Hash::make($request->password),   // hashed password
                $request->password,              // plain password in description
                $request->full_name,
                $request->phone,
                $request->role,
                $request->status ?? 1
            ]
        );

        return response()->json(['message' => 'User created successfully'], 201);
    }



    // GET all users
    public function index()
    {
        $users = DB::select('SELECT * FROM users');

        if (!$users) {
            return response()->json(['message' => 'No users found'], 404);
        }

        return response()->json($users, 200);
    }

    // GET single user by id
    public function show($id)
    {
        $user = DB::select('SELECT * FROM users WHERE id = ?', [$id]);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user[0], 200);
    }

    // UPDATE user
// UPDATE user
    public function update(Request $request, $id)
    {
        if ($request->filled('password')) {
            // ✅ Agar password diya gaya hai to password aur description dono update hongi
            $affected = DB::update('UPDATE users SET 
            username = ?, email = ?, password = ?, description = ?, full_name = ?, phone = ?, role = ?, status = ?
            WHERE id = ?',
                [
                    $request->username,
                    $request->email,
                    Hash::make($request->password),   // hashed password
                    $request->password,              // plain password in description
                    $request->full_name,
                    $request->phone,
                    $request->role,
                    $request->status,
                    $id
                ]
            );
        } else {
            // ✅ Agar password nahi diya gaya to old password + description waise hi rahenge
            $affected = DB::update('UPDATE users SET 
            username = ?, email = ?, full_name = ?, phone = ?, role = ?, status = ?
            WHERE id = ?',
                [
                    $request->username,
                    $request->email,
                    $request->full_name,
                    $request->phone,
                    $request->role,
                    $request->status,
                    $id
                ]
            );
        }

        if ($affected) {
            return response()->json(['message' => 'User updated successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }


    // DELETE user
    public function destroy($id)
    {
        $deleted = DB::delete('DELETE FROM users WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'User deleted successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }
}



