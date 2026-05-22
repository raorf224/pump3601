<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeSalaryManagementController extends Controller
{
    // ✅ Get all employee salary assignments
    public function index(Request $request)
    {
        $query = DB::table('employe_salary_management as esm')
    ->join('employees as e', function ($join) {
        $join->on('esm.emloye_id', '=', 'e.id')
             ->orOn('esm.emloye_id', '=', 'e.stationrow_id');
    })
    ->join('users as u', function ($join) {
        $join->on('e.user_id', '=', 'u.id')
             ->orOn('e.user_id', '=', 'u.stationrow_id');
    })
    ->join('stations as s', 'e.station_id', '=', 's.id')
    ->join('salary_componenet as sc', function ($join) {
        $join->on('esm.component_id', '=', 'sc.id')
             ->orOn('esm.component_id', '=', 'sc.stationrow_id');
    })
    ->select(
        'e.id as employee_id',
        'u.full_name as employee_name',
        's.name as station_name',
        'e.salary as employee_salary',
        'esm.status',
        'esm.created_at',
        DB::raw('GROUP_CONCAT(CONCAT(sc.component_name, " (", sc.type, " - ", sc.calculation, " - ", sc.cal_ammount, IF(sc.calculation="Percentage","%","Rs."), ")") SEPARATOR ", ") as components')
    )
    ->groupBy(
        'e.id',
        'u.full_name',
        's.name',
        'e.salary',
        'esm.status',
        'esm.created_at'
    )
    ->orderByDesc('e.id');

        // Filter by employee_id if provided
        if ($request->has('employee_id')) {
            $query->where('e.id', $request->employee_id);
        }

        $assignments = $query->get();

        return response()->json(['data' => $assignments]);
    }

public function index1(Request $request, $user_id)
{
    $userId = $user_id; // route parameter

    $query = DB::table('employe_salary_management as esm')
    ->join('employees as e', function ($join) {
        $join->on('esm.emloye_id', '=', 'e.id')
             ->orOn('esm.emloye_id', '=', 'e.stationrow_id');
    })
    ->join('stations as s','e.station_id', '=', 's.id')
    ->join('users as emp_user', function ($join) {
        $join->on('e.user_id', '=', 'emp_user.id')
             ->orOn('e.user_id', '=', 'emp_user.stationrow_id');
    })
    ->join('salary_componenet as sc', function ($join) {
        $join->on('esm.component_id', '=', 'sc.id')
             ->orOn('esm.component_id', '=', 'sc.stationrow_id');
    })
    ->select(
        'e.id as employee_id',
        'emp_user.full_name as employee_name',
        's.name as station_name',
        'e.salary as employee_salary',
        'esm.status',
        'esm.created_at',
        DB::raw('GROUP_CONCAT(CONCAT(sc.component_name, " (", sc.type, " - ", sc.calculation, " - ", sc.cal_ammount, IF(sc.calculation="Percentage","%","Rs."), ")") SEPARATOR ", ") as components')
    )
    ->when($userId, function ($q) use ($userId) {
        return $q->where('s.user_id', $userId);
    })
    ->groupBy(
        'e.id',
        'emp_user.full_name',
        's.name',
        'e.salary',
        'esm.status',
        'esm.created_at'
    )
    ->orderByDesc('e.id');

    if ($request->has('employee_id')) {
        $query->where('e.id', $request->employee_id);
    }

    $assignments = $query->get();

    return response()->json([
        'data' => $assignments ?: []  // Always return "data" array
    ]);
}






    public function By_status(Request $request)
    {
        $query =DB::table('employe_salary_management as esm')
    ->join('employees as e', function ($join) {
        $join->on('esm.emloye_id', '=', 'e.id')
             ->orOn('esm.emloye_id', '=', 'e.stationrow_id');
    })
    ->join('users as u', function ($join) {
        $join->on('e.user_id', '=', 'u.id')
             ->orOn('e.user_id', '=', 'u.stationrow_id');
    })
    ->join('stations as s','e.station_id', '=', 's.id')
    ->join('salary_componenet as sc', function ($join) {
        $join->on('esm.component_id', '=', 'sc.id')
             ->orOn('esm.component_id', '=', 'sc.stationrow_id');
    })
    ->where('esm.status', '=', 'Active')
    ->select(
        'e.id as employee_id',
        'u.full_name as employee_name',
        's.name as station_name',
        'e.salary as employee_salary',
        'esm.status',
        'esm.created_at',
        DB::raw('GROUP_CONCAT(CONCAT(sc.component_name, " (", sc.type, " - ", sc.calculation, " - ", sc.cal_ammount, IF(sc.calculation="Percentage","%","Rs."), ")") SEPARATOR ", ") as components')
    )
    ->groupBy(
        'e.id',
        'u.full_name',
        's.name',
        'e.salary',
        'esm.status',
        'esm.created_at'
    )
    ->orderByDesc('e.id');

        // Filter by employee_id if provided
        if ($request->has('employee_id')) {
            $query->where('e.id', $request->employee_id);
        }

        $assignments = $query->get();

        return response()->json(['data' => $assignments]);
    }

    // ✅ Get components for specific employee
    public function getEmployeeComponents($employeeId)
    {
        $components = DB::table('employe_salary_management')
            ->where('emloye_id', $employeeId)
            ->select('component_id')
            ->get();

        return response()->json(['data' => $components]);
    }

    // ✅ Delete all assignments for an employee
    public function deleteAll($employeeId)
    {
        try {
            $deleted = DB::table('employe_salary_management')
                ->where('emloye_id', $employeeId)
                ->delete();

            if ($deleted) {
                return response()->json(['message' => 'All components deleted successfully']);
            } else {
                return response()->json(['error' => 'No assignments found for this employee'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete assignments: ' . $e->getMessage()], 500);
        }
    }


    // ✅ Get single assignment by ID
    public function show($id)
    {
        $assignment = $assignment = DB::table('employe_salary_management as esm')
    ->join('employees as e', function ($join) {
        $join->on('esm.emloye_id', '=', 'e.id')
             ->orOn('esm.emloye_id', '=', 'e.stationrow_id');
    })
    ->join('salary_componenet as sc', function ($join) {
        $join->on('esm.component_id', '=', 'sc.id')
             ->orOn('esm.component_id', '=', 'sc.stationrow_id');
    })
    ->join('users as u', function ($join) {
        $join->on('e.user_id', '=', 'u.id')
             ->orOn('e.user_id', '=', 'u.stationrow_id');
    })
    ->join('stations as s', 'e.station_id', '=', 's.id')
    ->select(
        'esm.id',
        'esm.emloye_id',
        'esm.component_id',
        'esm.status',
        'u.full_name as employee_name',
        's.name as station_name',
        'e.salary as employee_salary',
        'sc.component_name'
    )
    ->where('esm.id', $id)
    ->first();

        if (!$assignment) {
            return response()->json(['error' => 'Assignment not found'], 404);
        }

        return response()->json($assignment);
    }

    // ✅ Create new employee salary assignment
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emloye_id' => 'required|integer|exists:employees,id',
            'component_ids' => 'required|array|min:1',
            'component_ids.*' => 'required|integer|exists:salary_componenet,id',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $assignments = [];
            foreach ($request->component_ids as $component_id) {
                // Check if assignment already exists
                $existing = DB::table('employe_salary_management')
                    ->where('emloye_id', $request->emloye_id)
                    ->where('component_id', $component_id)
                    ->first();

                if (!$existing) {
                    $assignments[] = [
                        'emloye_id' => $request->emloye_id,
                        'component_id' => $component_id,
                        'status' => $request->status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($assignments)) {
                DB::table('employe_salary_management')->insert($assignments);
                return response()->json(['message' => 'Salary components assigned successfully'], 201);
            } else {
                return response()->json(['error' => 'All selected components are already assigned to this employee'], 422);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to assign components: ' . $e->getMessage()], 500);
        }
    }

    public function updateEmployee(Request $request, $employeeId)
    {
        $validator = Validator::make($request->all(), [
            'component_ids' => 'required|array|min:1',
            'component_ids.*' => 'required|integer|exists:salary_componenet,id',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Delete all existing assignments for this employee
            DB::table('employe_salary_management')
                ->where('emloye_id', $employeeId)
                ->delete();

            // Create new assignments
            $assignments = [];
            foreach ($request->component_ids as $component_id) {
                $assignments[] = [
                    'emloye_id' => $employeeId,
                    'component_id' => $component_id,
                    'status' => $request->status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('employe_salary_management')->insert($assignments);
            DB::commit();

            return response()->json(['message' => 'Employee salary components updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update components: ' . $e->getMessage()], 500);
        }
    }



    // ✅ Get employees for dropdown
    public function getEmployees()
    {
        $employees = DB::table('employees as e')
    ->join('users as u', function ($join) {
        $join->on('e.user_id', '=', 'u.id')
             ->orOn('e.user_id', '=', 'u.stationrow_id');
    })
    ->join('stations as s', 'e.station_id', '=', 's.id')
    ->select(
        'e.id as employee_id',
        'u.full_name as user_full_name',
        's.name as station_name',
        'e.salary'
    )
    ->where('e.status', 'active')
    ->get();

        return response()->json($employees);
    }

// ✅ Get employees for dropdown
public function getEmployees_byuserid($user_id)
{
    $employees = DB::table('employees as e')
    ->join('users as u', function ($join) {
        $join->on('e.user_id', '=', 'u.id')
             ->orOn('e.user_id', '=', 'u.stationrow_id');
    })
    ->join('stations as s','e.station_id', '=', 's.id')
    ->select(
        'e.id as employee_id',
        'u.full_name as user_full_name',
        's.name as station_name',
        'e.salary',
        'e.user_id',
        'e.station_id'
    )
    ->where('s.user_id', $user_id)
    ->where('e.status', 'active')
    ->get();

    return response()->json($employees);
}

    // ✅ Get salary components for dropdown
    public function getSalaryComponents()
    {
        $components = DB::table('salary_componenet')
            ->select('id', 'component_name', 'type', 'calculation', 'cal_ammount')
            ->where('status', 'Active')
            ->get();

        return response()->json(['data' => $components]);
    }

    // ✅ Get employees by employee user_id (for employee login)
public function getEmployeesByEmployeeUser($employee_user_id)
{
    // Pehle employee ki station find karo
    $employee = DB::table('employees as e')
        ->where('e.user_id', $employee_user_id)
        ->select('e.station_id')
        ->first();

    if (!$employee) {
        return response()->json([]);
    }

    $employees = DB::table('employees as e')
    ->join('users as u', function ($join) {
        $join->on('e.user_id', '=', 'u.id')
             ->orOn('e.user_id', '=', 'u.stationrow_id');
    })
    ->join('stations as s', 'e.station_id', '=', 's.id')
    ->select(
        'e.id as employee_id',
        'u.full_name as user_full_name',
        's.name as station_name',
        'e.salary'
    )
    ->where('e.station_id', $employee->station_id)
    ->where('e.status', 'active')
    ->get();

    return response()->json($employees);
}

// ✅ Get employee salary assignments by employee user_id
public function getByEmployeeUser($employee_user_id)
{
    // Pehle employee ki station find karo
    $employee = DB::table('employees as e')
        ->where('e.user_id', $employee_user_id)
        ->select('e.station_id')
        ->first();

    if (!$employee) {
        return response()->json(['data' => []]);
    }

    $assignments = DB::table('employe_salary_management as esm')
    ->join('employees as e', function ($join) {
        $join->on('esm.emloye_id', '=', 'e.id')
             ->orOn('esm.emloye_id', '=', 'e.stationrow_id');
    })
    ->join('stations as s', 'e.station_id', '=', 's.id')
    ->join('users as emp_user', function ($join) {
        $join->on('e.user_id', '=', 'emp_user.id')
             ->orOn('e.user_id', '=', 'emp_user.stationrow_id');
    })
    ->join('salary_componenet as sc', function ($join) {
        $join->on('esm.component_id', '=', 'sc.id')
             ->orOn('esm.component_id', '=', 'sc.stationrow_id');
    })
    ->select(
        'e.id as employee_id',
        'emp_user.full_name as employee_name',
        's.name as station_name',
        'e.salary as employee_salary',
        'esm.status',
        'esm.created_at',
        DB::raw('GROUP_CONCAT(CONCAT(sc.component_name, " (", sc.type, " - ", sc.calculation, " - ", sc.cal_ammount, IF(sc.calculation="Percentage","%","Rs."), ")") SEPARATOR ", ") as components')
    )
    ->where('e.station_id', $employee->station_id)
    ->groupBy(
        'e.id',
        'emp_user.full_name',
        's.name',
        'e.salary',
        'esm.status',
        'esm.created_at'
    )
    ->orderByDesc('e.id')
    ->get();

    return response()->json([
        'data' => $assignments ?: []
    ]);
}


}