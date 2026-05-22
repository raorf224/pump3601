<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {


        $user = Auth::user();

        // ✅ 1. If not logged in → redirect to login
        if (!$user) {
            return redirect('/login');
        }

        // ✅ 2. Only check permissions if role = 'employee'
        if ($user->role === 'employee') {
            // Get role ID from employees table
            $role = DB::select("
                SELECT r.id 
                FROM employees e
                JOIN roles r 
                    ON e.role COLLATE utf8mb4_unicode_ci = r.name COLLATE utf8mb4_unicode_ci
                WHERE e.user_id = ?
                LIMIT 1
            ", [$user->id]);

            $roleId = $role[0]->id ?? null;

            if (!$roleId) {
                abort(403, 'Role not found.');
            }

            // ✅ Get all permissions for this role
            $permissions = DB::table('role_has_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->where('role_has_permissions.role_id', $roleId)
                ->pluck('permissions.name')
                ->toArray();

            // ✅ Check if the required permission exists
            if (!in_array($permission, $permissions)) {
                abort(403, 'You do not have permission to access this page.');
            }
        }

        // ✅ Allow request to continue
        return $next($request);
    }
}
