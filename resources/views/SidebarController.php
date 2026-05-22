<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class SidebarController extends Controller
{
    public function getSidebar($userId)
{
    $permissions = DB::table('employees as e')
    ->join('roles as r', DB::raw('r.name COLLATE utf8mb4_general_ci'), '=', DB::raw('e.role COLLATE utf8mb4_general_ci'))
    ->join('role_has_permissions as rp', 'rp.role_id', '=', 'r.id')
    ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
    ->where('e.user_id', $userId)
    ->pluck('p.name')
    ->toArray();

    return response()->json([
        'pages' => $permissions
    ]);
}
}