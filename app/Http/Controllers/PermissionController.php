<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use DataTables;
use DB;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Permission::latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary editPermission" data-id="'.$row->id.'">Edit</button>
                        <button class="btn btn-sm btn-danger deletePermission" data-id="'.$row->id.'">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('permissions.index');
    }

 public function assignPermissions(Request $request, $roleId)
{
    $request->validate([
        'permissions' => 'array|required',
        'permissions.*.id' => 'integer|exists:permissions,id',
        'permissions.*.create' => 'boolean',
        'permissions.*.read' => 'boolean',
        'permissions.*.update' => 'boolean',
        'permissions.*.delete' => 'boolean',
    ]);

    try {
        // Delete old permissions for this role
        DB::table('role_has_permissions')->where('role_id', $roleId)->delete();

        // Prepare new permissions
        $insertData = collect($request->permissions)->map(function ($p) use ($roleId) {
            return [
                'role_id' => $roleId,
                'permission_id' => $p['id'],
                'create' => $p['create'] ?? 0,
                'read' => $p['read'] ?? 0,
                'update' => $p['update'] ?? 0,
                'delete' => $p['delete'] ?? 0,
            ];
        })->toArray();

        if (!empty($insertData)) {
            DB::table('role_has_permissions')->insert($insertData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully.',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error assigning permissions.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function get()
    {
        $request=DB::select('select * from permissions');
        return response()->json($request);
    }
   public function getrolespermission($id)
{
    $permissions = DB::table('role_has_permissions as rp')
        ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
        ->where('rp.role_id', $id)
        ->select(
            'p.id',
            'p.name',
            'rp.create',
            'rp.read',
            'rp.update',
            'rp.delete'
        )
        ->get();

    return response()->json($permissions);
}
 public function getpermissionbyuserid($id, $role)
{
    if ($role == 'employee') {
        $user = DB::select("
            SELECT r.*
            FROM users u
            JOIN employees e ON u.id = e.user_id
            JOIN roles r ON e.role COLLATE utf8mb4_unicode_ci = r.name COLLATE utf8mb4_unicode_ci
            WHERE u.id = ?", [$id]
        );

        if (empty($user)) {
            return response()->json([]);
        }

        $permissions = DB::table('role_has_permissions as rp')
            ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
            ->where('rp.role_id', $user[0]->id)
            ->select('p.id', 'p.name', 'rp.create', 'rp.read', 'rp.update', 'rp.delete')
            ->get();

        return response()->json($permissions);
    } 
    else {
        $permission = '[
            {"id":1,"name":"users","create":1,"read":1,"update":1,"delete":1},
            {"id":2,"name":"sites","create":1,"read":1,"update":1,"delete":1},
            {"id":3,"name":"product","create":1,"read":1,"update":1,"delete":1},
            {"id":4,"name":"tanks","create":1,"read":1,"update":1,"delete":1},
            {"id":5,"name":"dispenser","create":1,"read":1,"update":1,"delete":1},
            {"id":6,"name":"nozzels","create":1,"read":1,"update":1,"delete":1},
            {"id":7,"name":"oilpurchase","create":1,"read":1,"update":1,"delete":1},
            {"id":8,"name":"accounts","create":1,"read":1,"update":1,"delete":1},
            {"id":9,"name":"transactions","create":1,"read":1,"update":1,"delete":1},
            {"id":10,"name":"employees","create":1,"read":1,"update":1,"delete":1},
            {"id":11,"name":"employee_payroll","create":1,"read":1,"update":1,"delete":1},
            {"id":12,"name":"shifts","create":1,"read":1,"update":1,"delete":1},
            {"id":13,"name":"attendance","create":1,"read":1,"update":1,"delete":1},
            {"id":14,"name":"account_report","create":1,"read":1,"update":1,"delete":1},
            {"id":16,"name":"store_setup","create":1,"read":1,"update":1,"delete":1},
            {"id":17,"name":"product_setup","create":1,"read":1,"update":1,"delete":1},
            {"id":18,"name":"pos","create":1,"read":1,"update":1,"delete":1},
            {"id":19,"name":"lubricants","create":1,"read":1,"update":1,"delete":1}
        ]';

        return response()->json(json_decode($permission));
    }
}


    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->update(['name' => $request->name]);
        return response()->json(['success' => 'Permission updated successfully']);
    }

    public function destroy($id)
    {
        Permission::findOrFail($id)->delete();
        return response()->json(['success' => 'Permission deleted successfully']);
    }

    public function edit($id)
    {
        return response()->json(Permission::findOrFail($id));
    }
}