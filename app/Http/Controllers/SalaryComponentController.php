<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Auth;

class SalaryComponentController extends Controller
{
    // ✅ Get all components
    public function index()
    {
        $components = DB::table('salary_componenet') // Fixed table name
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $components]);
    }

    // ✅ Get single component by ID
    public function show($id)
    {
        $component = DB::table('salary_componenet')->where('id', $id)->first();
        if (!$component) {
            return response()->json(['error' => 'Component not found'], 404);
        }
        return response()->json($component);
    }

    // ✅ Create new component (separate function)
    public function store(Request $request)
    {
  
        $validator = Validator::make($request->all(), [
            'component_name' => 'required|string',
            'type' => 'required|string|in:Earning,Deduction',
            'calculation' => 'required|string|in:Percentage,Fixed',
            'cal_ammount' => 'required|numeric',
            'mandatory' => 'required|string|in:Yes,No',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            DB::table('salary_componenet')->insert([
                'station_id' => $request->station_id,
                'component_name' => $request->component_name,
                'type' => $request->type,
                'calculation' => $request->calculation,
                'cal_ammount' => $request->cal_ammount,
                'mandatory' => $request->mandatory,
                'status' => $request->status,
                'created_at' => now(),
            ]);

            return response()->json(['message' => 'Salary Component created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create component: ' . $e->getMessage()], 500);
        }
    }

    // ✅ Update existing component (separate function)
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'component_name' => 'required|string',
            'type' => 'required|string|in:Earning,Deduction',
            'calculation' => 'required|string|in:Percentage,Fixed',
            'cal_ammount' => 'required|numeric',
            'mandatory' => 'required|string|in:Yes,No',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $component = DB::table('salary_componenet')->where('id', $id)->first();
        if (!$component) {
            return response()->json(['error' => 'Component not found'], 404);
        }

        try {
            DB::table('salary_componenet')
                ->where('id', $id)
                ->update([
                    'component_name' => $request->component_name,
                    'type' => $request->type,
                    'calculation' => $request->calculation,
                    'cal_ammount' => $request->cal_ammount,
                    'mandatory' => $request->mandatory,
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);

            return response()->json(['message' => 'Salary Component updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update component: ' . $e->getMessage()], 500);
        }
    }

    // ✅ Delete component
    public function destroy($id)
    {
        try {
            $component = DB::table('salary_componenet')->where('id', $id)->first();
            if (!$component) {
                return response()->json(['error' => 'Component not found'], 404);
            }

            DB::table('salary_componenet')->where('id', $id)->delete();
            return response()->json(['message' => 'Component deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete component: ' . $e->getMessage()], 500);
        }
    }

    // ✅ Toggle Active/Inactive
    public function toggleStatus($id)
    {
        $component = DB::table('salary_componenet')->where('id', $id)->first();
        if (!$component) {
            return response()->json(['error' => 'Component not found'], 404);
        }

        $newStatus = $component->status === 'Active' ? 'Inactive' : 'Active';
        DB::table('salary_componenet')->where('id', $id)->update(['status' => $newStatus]);

        return response()->json(['message' => "Status updated to {$newStatus}", 'status' => $newStatus]);
    }
}