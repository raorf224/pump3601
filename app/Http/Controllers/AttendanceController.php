<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $filterDate = $request->query('date', date('Y-m-d'));
        $role = $request->query('role');
        $status = $request->query('status');

        $query = "
        SELECT 
    a.id AS attendance_id,
    e.id AS employee_id,
    u.username AS employee_name,
    e.role AS designation,
    e.station_id, 
    s.name AS station_name,
    a.shift_id,
    CASE 
        WHEN sh.shift_no = 1 THEN 'Day'
        WHEN sh.shift_no = 2 THEN 'Night'
        ELSE 'N/A'
    END AS shift_name,
    a.check_in,
    a.check_out,
    a.status,
    a.remarks,
    a.date

FROM employees e

LEFT JOIN users u 
    ON e.user_id = u.id 
    OR e.user_id = u.stationrow_id

LEFT JOIN stations s 
    ON e.station_id = s.id 
    

LEFT JOIN attendance a 
    ON (e.id = a.employee_id OR e.stationrow_id = a.employee_id)
    AND a.date = ?

LEFT JOIN shifts sh 
    ON a.shift_id = sh.id 
    OR a.shift_id = sh.stationrow_id

WHERE 1=1

    ";

        $bindings = [$filterDate];

        if (!empty($role)) {
            $query .= " AND e.role = ?";
            $bindings[] = $role;
        }

        if (!empty($status)) {
            $query .= " AND a.status = ?";
            $bindings[] = $status;
        }

        // ORDER BY sirf ek daalna hai yahan
        $query .= " ORDER BY e.id ASC, a.date DESC";

        $records = DB::select($query, $bindings);

        return response()->json($records);
    }

public function index1($user_id, Request $request)
{
    $filterDate = $request->query('date', date('Y-m-d'));
    $role = $request->query('role');
    $status = $request->query('status');

    $query = "
        SELECT 
            a.id AS attendance_id,
            e.id AS employee_id,
            u.username AS employee_name,
            e.role AS designation,
            e.station_id, 
            s.name AS station_name,
            s.user_id AS station_user_id,
            a.shift_id,
            CASE 
                WHEN sh.shift_no = 1 THEN 'Day'
                WHEN sh.shift_no = 2 THEN 'Night'
                ELSE 'N/A'
            END AS shift_name,
            a.check_in,
            a.check_out,
            a.status,
            a.remarks,
            a.date
        FROM employees e
        LEFT JOIN users u 
    ON e.user_id = u.id 
    OR e.user_id = u.stationrow_id

LEFT JOIN stations s 
    ON e.station_id = s.id 
    

LEFT JOIN attendance a 
    ON (e.id = a.employee_id OR e.stationrow_id = a.employee_id)
    AND a.date = ?

LEFT JOIN shifts sh 
    ON a.shift_id = sh.id 
    OR a.shift_id = sh.stationrow_id

        WHERE s.user_id = ?
    ";

    $bindings = [$filterDate, $user_id];

    if (!empty($role)) {
        $query .= " AND e.role = ?";
        $bindings[] = $role;
    }

    if (!empty($status)) {
        $query .= " AND a.status = ?";
        $bindings[] = $status;
    }

    $query .= " ORDER BY e.id ASC, a.date DESC";

    $records = DB::select($query, $bindings);

    return response()->json($records);
}



    public function show($id)
    {
        $record = DB::select('
    SELECT 
        a.id AS attendance_id,
        a.employee_id,
        u.username AS employee_name,
        e.role AS designation,
        a.station_id,
        s.name AS station_name,
        a.shift_id,
        CASE 
            WHEN sh.shift_no = 1 THEN \'Day\'
            WHEN sh.shift_no = 2 THEN \'Night\'
            ELSE \'N/A\'
        END AS shift_name,
        a.date,
        a.check_in,
        a.check_out,
        a.status,
        a.remarks,
        a.created_at,
        a.updated_at
    FROM attendance a
   LEFT JOIN users u 
    ON e.user_id = u.id 
    OR e.user_id = u.stationrow_id

LEFT JOIN stations s 
    ON e.station_id = s.id 
    

LEFT JOIN attendance a 
    ON (e.id = a.employee_id OR e.stationrow_id = a.employee_id)
    AND a.date = ?

LEFT JOIN shifts sh 
    ON a.shift_id = sh.id 
    OR a.shift_id = sh.stationrow_id
    WHERE a.id = ?
', [$id]);



        if (empty($record)) {
            return response()->json(['message' => 'Attendance record not found'], 404);
        }

        return response()->json($record[0]);
    }


    public function getActiveEmployees()
    {
        $employees = DB::select("
            SELECT 
                e.id,
                u.username,
                e.role,
                e.station_id,
                s.name as station_name
            FROM employees e
          LEFT JOIN users u 
    ON e.user_id = u.id 
    OR e.user_id = u.stationrow_id
      LEFT JOIN stations s 
    ON e.station_id = s.id 
            WHERE e.status = 'active'
            ORDER BY u.username
        ");
        return response()->json($employees);
    }

    public function store(Request $request)
{
    // Validate first (important)
    $validatedData = $request->validate([
        'employee_id' => 'required|integer',
        'shift_id'    => 'required|integer',
        'date'        => 'required|date',
        'status'      => 'required|string',
        'check_in'    => 'nullable',
        'check_out'   => 'nullable',
        'remarks'     => 'nullable|string'
    ]);

    // Get employee
    $employee = DB::table('employees')->where('id', $request->employee_id)->first();
    if (!$employee) {
        return response()->json(['error' => 'Employee not found'], 404);
    }

    // Check duplicate
    $existing = DB::table('attendance')
        ->where('employee_id', $request->employee_id)
        ->where('date', $request->date)
        ->first();

    if ($existing) {
        return response()->json([
            'error' => 'Attendance already exists for this employee on the selected date',
            'attendance_id' => $existing->id
        ], 409);
    }

    // Prepare insert data
    $attendanceData = [
        'employee_id' => $request->employee_id,
        'station_id'  => $employee->station_id,
        'shift_id'    => $request->shift_id,
        'date'        => $request->date,
        'check_in'    => $request->check_in,
        'check_out'   => $request->check_out,
        'status'      => $request->status,
        'remarks'     => $request->remarks,
        'created_at'  => now(),
        'updated_at'  => now(),
    ];

    // Insert attendance
    $attendanceId = DB::table('attendance')->insertGetId($attendanceData);

    // Save to sync log
    // DB::table('synclog')->insert([
    //     'table_name' => 'attendance',
    //     'record_id'  => $attendanceId,
    //     'action'     => 'insert',
    //     'data'       => json_encode($attendanceData),
    //     'created_at' => now()
    // ]);

    return response()->json([
        'success' => true,
        'message' => 'Attendance created successfully',
        'attendance_id' => $attendanceId
    ], 201);
}

    public function update(Request $request, $id)
{
    $attendance = DB::table('attendance')->where('id', $id)->first();
    if (!$attendance) {
        return response()->json(['message' => 'Attendance record not found'], 404);
    }

    $updateData = [];
    $allowedFields = ['employee_id', 'shift_id', 'date', 'check_in', 'check_out', 'status', 'remarks'];

    foreach ($allowedFields as $field) {
        if ($request->has($field)) {
            $updateData[$field] = $request->$field;
        }
    }

    // If employee changed → update station_id automatically
    if (isset($updateData['employee_id'])) {
        $employee = DB::table('employees')->where('id', $updateData['employee_id'])->first();
        if ($employee) {
            $updateData['station_id'] = $employee->station_id;
        }
    }

    if (empty($updateData)) {
        return response()->json(['message' => 'No fields to update'], 400);
    }

    $updateData['updated_at'] = now();

    // Update attendance
    DB::table('attendance')->where('id', $id)->update($updateData);

    // 🔥 Get full updated record (IMPORTANT for sync)
    $updatedRecord = DB::table('attendance')->where('id', $id)->first();

    // // Save to sync log
    // DB::table('synclog')->insert([
    //     'table_name' => 'attendance',
    //     'record_id'  => $id,
    //     'action'     => 'update',
    //     'data'       => json_encode($updatedRecord), // FULL record
    //     'created_at' => now()
    // ]);

    return response()->json([
        'message' => 'Attendance updated successfully',
        'attendance_id' => $id
    ]);
}

    public function destroy($id)
    {
        $attendance = DB::table('attendance')->where('id', $id)->first();
        if (!$attendance) {
            return response()->json(['message' => 'Attendance record not found'], 404);
        }
        DB::update('delete from attendance  where id=?',[$id]);
        // $data=['station_id'=>$attendance->station_id];
        // DB::insert("INSERT INTO `synclog`( `table_name`, `record_id`, `action`) VALUES ('accounts',?,'delete')",[$id,$data]);

        return response()->json(['message' => 'Attendance deleted successfully', 'deleted_id' => $id]);
    }

    public function getTodaySummary()
    {
        $today = date('Y-m-d');
        $summary = DB::select("
            SELECT 
                COUNT(*) as total_employees,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'leave' THEN 1 ELSE 0 END) as leave_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN a.status IS NULL THEN 1 ELSE 0 END) as not_marked_count
            FROM employees e
            
           LEFT JOIN attendance a 
    ON (e.id = a.employee_id OR e.stationrow_id = a.employee_id) AND a.date = ?
            WHERE e.status = 'active'
        ", [$today]);

        return response()->json($summary[0]);
    }
}