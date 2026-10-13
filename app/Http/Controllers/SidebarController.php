<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SidebarController extends Controller
{
    public function getSidebar($userId)
    {
        $user = Auth::user();
        if (!$user || (int) $user->id !== (int) $userId) {
            abort(401, 'Unauthenticated.');
        }

        $roleName = $user->role;
        if ($user->role === 'employee') {
            $roleName = DB::table('employees')
                ->where('user_id', $user->id)
                ->value('role');

            if (!$roleName) {
                return response()->json(['pages' => []]);
            }
        } elseif (!in_array($user->role, ['rm', 'tm'], true)) {
            return response()->json(['pages' => []]);
        }

        $roleId = DB::table('roles')
            ->where('name', $roleName)
            ->value('id');

        if (!$roleId) {
            return response()->json(['pages' => []]);
        }

        $permissions = DB::table('role_has_permissions as rp')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->where('rp.read', 1)
            ->orderBy('p.name')
            ->pluck('p.name')
            ->all();

        return response()->json(['pages' => $permissions]);
    }
}