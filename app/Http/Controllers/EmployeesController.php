<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeesController extends Controller
{
    // Get all employees with full details (joins with users and stations)
    public function index()
    {
        $employees = DB::select("
            SELECT 
    e.id AS employee_id,
    e.role AS employee_role,
    e.address,
    e.city,
    e.region,
    e.country,
    e.cnic,
    e.phone,
    e.salary,
    e.status AS employee_status,
    e.created_at,
    e.updated_at,
    u.username AS user_name,
    u.email AS user_email,
    u.full_name AS user_full_name,
    s.name AS station_name,
    s.location AS station_location
FROM employees e
LEFT JOIN users u 
    ON e.user_id = u.id 
    OR e.user_id = u.stationrow_id
LEFT JOIN stations s 
    ON e.station_id = s.id 
ORDER BY e.created_at DESC;
        ");

        return response()->json($employees);
    }


        public function index1($user_id)
    {
        $employees = DB::select(
            'SELECT e.id AS employee_id, e.role AS employee_role, e.address, e.city, e.region, e.country, e.cnic, e.phone, 
                    e.salary, e.status AS employee_status, e.created_at, e.updated_at,
                    u.username AS user_name, u.email AS user_email, u.full_name AS user_full_name,
                    s.name AS station_name, s.location AS station_location, s.user_id as station_user_id
             FROM employees e
            LEFT JOIN users u 
            ON e.user_id = u.id 
            OR e.user_id = u.stationrow_id
             LEFT JOIN stations s ON e.station_id = s.id
                WHERE s.user_id = ?
             ORDER BY e.created_at DESC  
            ', [$user_id]);

        return response()->json($employees);
    }

    // Get a single employee by ID with full details
    public function show($id)
    {
        $employee = DB::select(
            'SELECT e.id AS employee_id,e.station_id, e.role AS employee_role, e.address, e.city, e.region, e.country, e.cnic, e.phone, 
                    e.salary, e.status AS employee_status, e.created_at, e.updated_at,
                    u.username AS user_name, u.email AS user_email, u.full_name AS user_full_name,
                    s.name AS station_name, s.location AS station_location
             FROM employees e
             LEFT JOIN users u 
                ON e.user_id = u.id 
                OR e.user_id = u.stationrow_id
             LEFT JOIN stations s ON e.station_id = s.id
             WHERE e.id = ?',
            [$id]
        );

        if (empty($employee)) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee[0]);
    }
	
		public function show_station_id($station_id)
{
    $employees = DB::select(
        'SELECT 
            e.id AS employee_id,
            e.station_id,
            e.role AS employee_role,
            e.address,
            e.city,
            e.region,
            e.country,
            e.cnic,
            e.phone,
            e.salary,
            e.status AS employee_status,
            e.created_at,
            e.updated_at,
            u.username AS user_name,
            u.email AS user_email,
            u.full_name AS user_full_name,
            s.name AS station_name,
            s.location AS station_location
        FROM employees e
        LEFT JOIN users u 
            ON e.user_id = u.id 
            OR e.user_id = u.stationrow_id
        LEFT JOIN stations s ON e.station_id = s.id
        WHERE e.station_id = ?
        ORDER BY e.created_at DESC',
        [$station_id]
    );
			 // ✅ Return all employees for this station (not just the first)
    return response()->json($employees);
		}


    // Create a new employee (with user entry in users table)
    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:45',
            'role' => 'nullable|in:manager,cashier,pump_operator,other',
            'station_id' => 'required|integer',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:45',
            'region' => 'nullable|string|max:45',
            'country' => 'nullable|string|max:45',
            'cnic' => 'nullable|string|max:45',
            'salary' => 'required|numeric',
            'status' => 'nullable|in:active,inactive',
        ]);

        $userStatus = ($validatedData['status'] ?? 'active') === 'active' ? 1 : 0;


        DB::beginTransaction();

        try {
            // Insert into users table
            DB::insert(
                'INSERT INTO users (username, email, password, description, full_name, phone, role, status, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                [
                    $validatedData['username'],
                    $validatedData['email'],
                    bcrypt($validatedData['password']), // Hash the password
                    $validatedData['password'],           // ✅ clear password in description
                    $validatedData['full_name'],
                    $validatedData['phone'],
                    'employee', // Default role for users table
                    $userStatus, // ✅ integer (1/0)
                ]
            );

            // Get the last inserted user ID
            $userId = DB::getPdo()->lastInsertId();

            // Insert into employees table
            DB::insert(
                'INSERT INTO employees (user_id, station_id, role, address, city, region, country, cnic, phone, salary, status, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                [
                    $userId,
                    $validatedData['station_id'],
                    $validatedData['role'],
                    $validatedData['address'],
                    $validatedData['city'],
                    $validatedData['region'],
                    $validatedData['country'],
                    $validatedData['cnic'],
                    $validatedData['phone'],
                    $validatedData['salary'],
                    $validatedData['status'] ?? 'active',
                ]
            );

            DB::commit();

            return response()->json(['message' => 'Employee created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create employee', 'error' => $e->getMessage()], 500);
        }
    }

    // Update an existing employee
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'role' => 'nullable|required|in:manager,cashier,pump_operator,other',
            'station_id' => 'sometimes|required|integer',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:45',
            'region' => 'nullable|string|max:45',
            'country' => 'nullable|string|max:45',
            'cnic' => 'nullable|string|max:45',
            'phone' => 'nullable|string|max:45',
            'salary' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE employees SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Employee updated successfully']);
    }

    // Delete an employee
    public function destroy($id)
    {
		//dd('Samad');
        $deleted = DB::delete('DELETE FROM employees WHERE id = ?', [$id]);

        if ($deleted) {
            return response()->json(['message' => 'Employee deleted successfully']);
        }

        return response()->json(['message' => 'Employee not found'], 404);
    }
    public function showbystation($id)
    {
        $employees = DB::select('SELECT e.*, u.username, u.email, u.full_name, u.phone, e.role , s.name as station_name
        FROM employees e 
      LEFT JOIN users u 
    ON e.user_id = u.id 
    OR e.user_id = u.stationrow_id
        JOIN stations s on e.station_id = s.id
        WHERE e.station_id = ?', [$id]);

        if (empty($employees)) {
            return response()->json([], 200); // return empty array instead of 404
        }

        return response()->json($employees);
    }
}