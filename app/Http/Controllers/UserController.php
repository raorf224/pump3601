<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;


class UserController extends Controller
{
    // CREATE user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|max:25',
            'full_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,owner,employee,rm,tm',
            'status' => 'nullable|in:0,1',
        ]);

        if (in_array($validated['role'], ['rm', 'tm'], true)) {
            $this->ensurePermissionRole($validated['role']);
        }

        DB::insert('INSERT INTO users 
        (username, email, password, description, full_name, phone, role, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $validated['username'],
                $validated['email'],
                Hash::make($validated['password']),
                $validated['password'],
                $validated['full_name'],
                $validated['phone'] ?? null,
                $validated['role'],
                $validated['status'] ?? 1
            ]
        );

        return response()->json(['message' => 'User created successfully'], 201);
    }



    // GET all users
    public function index()
    {
        $users = DB::select('SELECT id, username, email, full_name, phone, role, status FROM users');
        return response()->json($users, 200);
    }

    public function byRole($role)
    {
        abort_unless(in_array($role, ['rm', 'tm'], true), 404);

        $users = DB::table('users')
            ->select('id', 'full_name', 'username')
            ->where('role', $role)
            ->where('status', 1)
            ->orderBy('full_name')
            ->get();

        return response()->json($users);
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
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,'.$id,
            'email' => 'required|email|max:100|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6|max:25',
            'full_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,owner,employee,rm,tm',
            'status' => 'required|in:0,1',
        ]);

        $existingUser = DB::table('users')->where('id', $id)->first();
        if (!$existingUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (in_array($validated['role'], ['rm', 'tm'], true)) {
            $this->ensurePermissionRole($validated['role']);
        }

        if ($request->filled('password')) {
            // ✅ Agar password diya gaya hai to password aur description dono update hongi
            $affected = DB::update('UPDATE users SET 
            username = ?, email = ?, password = ?, description = ?, full_name = ?, phone = ?, role = ?, status = ?
            WHERE id = ?',
                [
                    $validated['username'],
                    $validated['email'],
                    Hash::make($validated['password']),
                    $validated['password'],
                    $validated['full_name'],
                    $validated['phone'] ?? null,
                    $validated['role'],
                    $validated['status'],
                    $id
                ]
            );
        } else {
            // ✅ Agar password nahi diya gaya to old password + description waise hi rahenge
            $affected = DB::update('UPDATE users SET 
            username = ?, email = ?, full_name = ?, phone = ?, role = ?, status = ?
            WHERE id = ?',
                [
                    $validated['username'],
                    $validated['email'],
                    $validated['full_name'],
                    $validated['phone'] ?? null,
                    $validated['role'],
                    $validated['status'],
                    $id
                ]
            );
        }

        if ($existingUser->role === 'rm' && $validated['role'] !== 'rm') {
            DB::table('stations')->where('rm_user_id', $id)->update(['rm_user_id' => null]);
        }

        if ($existingUser->role === 'tm' && $validated['role'] !== 'tm') {
            DB::table('stations')->where('tm_user_id', $id)->update(['tm_user_id' => null]);
        }

        return response()->json(['message' => 'User updated successfully'], 200);
    }


    // DELETE user
    public function destroy($id)
    {
        DB::table('stations')->where('rm_user_id', $id)->update(['rm_user_id' => null]);
        DB::table('stations')->where('tm_user_id', $id)->update(['tm_user_id' => null]);
        $deleted = DB::delete('DELETE FROM users WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'User deleted successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    private function ensurePermissionRole(string $role): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $roleQuery = DB::table('roles')->where('name', $role);
        if (Schema::hasColumn('roles', 'guard_name')) {
            $roleQuery->where('guard_name', 'web');
        }

        if ($roleQuery->exists()) {
            return;
        }

        $roleData = ['name' => $role];
        if (Schema::hasColumn('roles', 'guard_name')) {
            $roleData['guard_name'] = 'web';
        }
        if (Schema::hasColumn('roles', 'created_at')) {
            $roleData['created_at'] = now();
        }
        if (Schema::hasColumn('roles', 'updated_at')) {
            $roleData['updated_at'] = now();
        }

        DB::table('roles')->insert($roleData);
    }
}
