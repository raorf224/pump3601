<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DataTables;
use DB;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Role::with('permissions')->latest()->get();
            return DataTables::of($data)
                ->addColumn('permissions', function ($row) {
                    return $row->permissions->pluck('name')->implode(', ');
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary editRole" data-id="'.$row->id.'">Edit</button>
                        <button class="btn btn-sm btn-danger deleteRole" data-id="'.$row->id.'">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $permissions = Permission::all();
        return view('roles', compact('permissions'));
    }

    public function get()
    {
        $request=DB::select('select * from roles');
        return response()->json($request);
    }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        return response()->json(['success' => 'Role created successfully']);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        return response()->json(['success' => 'Role updated successfully']);
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['success' => 'Role deleted successfully']);
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }
}
