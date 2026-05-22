<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    // Get all payroll records
    public function index()
    {
        $payrolls = DB::select(
            'SELECT p.id, p.employee_id, u.full_name AS employee_name,s.id AS station_id,s.name AS station_name, e.role, p.salary, p.payment_date, p.note, p.created_at
             FROM payroll p
             LEFT JOIN employees e ON p.employee_id = e.id OR p.employee_id = e.stationrow_id
             LEFT JOIN stations s ON e.station_id = s.id
             LEFT JOIN users u ON e.user_id = u.id OR e.user_id = u.stationrow_id
             ORDER BY p.payment_date DESC'
        );

        return response()->json($payrolls);
    }

    public function index1($user_id)
    {
        $payrolls = DB::select(
            'SELECT p.id, p.employee_id, u.full_name AS employee_name,s.id AS station_id,s.name AS station_name, e.role, p.salary, p.payment_date, p.note, p.created_at, s.user_id as station_user_id
             FROM payroll p
              LEFT JOIN employees e ON p.employee_id = e.id OR p.employee_id = e.stationrow_id
             LEFT JOIN stations s ON e.station_id = s.id
             LEFT JOIN users u ON e.user_id = u.id OR e.user_id = u.stationrow_id
                WHERE s.user_id = ?
             ORDER BY p.payment_date DESC
               ',
            [$user_id]
        );

        return response()->json($payrolls);
    }

    // Get payroll records for a specific employee
    public function getByEmployee($employeeId)
    {
        $payrolls = DB::select(
            'SELECT p.id, p.employee_id, u.full_name AS employee_name,s.id AS station_id,s.name AS station_name, e.role, p.salary, p.payment_date, p.note, p.created_at
             FROM payroll p
             LEFT JOIN employees e ON p.employee_id = e.id OR p.employee_id = e.stationrow_id
             LEFT JOIN stations s ON e.station_id = s.id
             LEFT JOIN users u ON e.user_id = u.id OR e.user_id = u.stationrow_id
             WHERE p.employee_id = ?
             ORDER BY p.payment_date DESC',
            [$employeeId]
        );

        return response()->json($payrolls);
    }

    // Create a new payroll record

    public function store(Request $request)
    {
        // Get employee_id from request - handle both employee_id and employee_id[] formats
        $employeeIds = $request->input('employee_id', []);

        // If employee_id is empty, try employee_id[] format (from FormData)
        if (empty($employeeIds)) {
            $employeeIds = $request->input('employee_id[]', []);
        }

        // If it's a string (single selection), convert to array
        if (!is_array($employeeIds)) {
            $employeeIds = [$employeeIds];
        }

        // Validate the request
        $validator = Validator::make([
            'station_id' => $request->station_id,
            'employee_id' => $employeeIds,
            'payment_date' => $request->payment_date,
            'note' => $request->note
        ], [
            'station_id' => 'required|exists:stations,id',
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required|exists:employees,id',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        $createdRecords = [];

        foreach ($validatedData['employee_id'] as $empId) {
            // Get individual salary for each employee
            $salary = $request->input("salaries.{$empId}");

            // If no individual salary provided, get from employee table
            if (!$salary) {
                $employee = DB::table('employees')->where('id', $empId)->first();
                $salary = $employee ? $employee->salary : 0;
            }

            // Check if payroll already exists for this employee on this date
            $existing = DB::table('payroll')
                ->where('employee_id', $empId)
                ->where('payment_date', $validatedData['payment_date'])
                ->first();

            if ($existing) {
                continue; // Skip if already exists
            }

            // Insert payroll record with individual salary
            $id = DB::table('payroll')->insertGetId([
                'employee_id' => $empId,
                'station_id' => $request->station_id,
                'salary' => $salary, // Individual salary for each employee
                'payment_date' => $validatedData['payment_date'],
                'note' => $validatedData['note'] ?? null,
                'created_at' => now(),
            ]);

            $createdRecords[] = $id;
        }

        if (empty($createdRecords)) {
            return response()->json([
                'message' => 'No payroll records were created. They may already exist for the selected date.'
            ], 400);
        }

        return response()->json([
            'message' => count($createdRecords) > 1
                ? count($createdRecords) . ' payroll records created successfully'
                : 'Payroll record created successfully',
            'record_ids' => $createdRecords
        ], 201);
    }

    // Get payroll detail with multiple employees (for view)
    public function view($id)
    {
        $payrolls = DB::select(
            'SELECT p.id, p.employee_id, u.full_name AS employee_name, e.role, s.name AS station_name, s.id as station_id,
                p.salary, p.payment_date, p.note
         FROM payroll p
         LEFT JOIN employees e ON p.employee_id = e.id OR p.employee_id = e.stationrow_id
             LEFT JOIN stations s ON e.station_id = s.id
             LEFT JOIN users u ON e.user_id = u.id OR e.user_id = u.stationrow_id
         WHERE p.id = ?',
            [$id]
        );

        return response()->json($payrolls);
    }

    // Delete a payroll record
    public function destroy($id)
    {
        $payroll = DB::select('SELECT * FROM payroll WHERE id = ?', [$id]);

        if (empty($payroll)) {
            return response()->json(['message' => 'Payroll record not found'], 404);
        }

        DB::delete('DELETE FROM payroll WHERE id = ?', [$id]);

        return response()->json(['message' => 'Payroll record deleted successfully']);
    }

    public function calculateDeduction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|date_format:Y-m',
            'station_id' => 'required|exists:stations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $employeeId = $request->employee_id;
        $month = $request->month;
        $stationId = $request->station_id;

        // Get employee basic salary
        $employee = DB::table('employees')
            ->where('id', $employeeId)
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $monthlySalary = $employee->salary;

        // Calculate working days in month (excluding Fridays)
        $year = date('Y', strtotime($month));
        $monthNum = date('m', strtotime($month));
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
        $workingDays = 0;

        for ($day = 1; $day <= $totalDays; $day++) {
            $currentDate = "$year-$monthNum-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $dayOfWeek = date('N', strtotime($currentDate));
            // Count only Sunday through Thursday (1-5) as working days, exclude Friday (6) and Saturday (7)
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $workingDays++;
            }
        }

        // Get attendance records for the month
        $attendanceRecords = DB::table('attendance')
            ->where('employee_id', $employeeId)
            ->where('station_id', $stationId)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->get();

        // Calculate deductions
        $absentDays = 0;
        $lateDays = 0;
        $halfDays = 0;
        $totalDeduction = 0;

        foreach ($attendanceRecords as $record) {
            $dayOfWeek = date('N', strtotime($record->date));

            // Skip Fridays and Saturdays for deduction calculation
            if ($dayOfWeek >= 6)
                continue;

            switch ($record->status) {
                case 'absent':
                    $absentDays++;
                    break;
                case 'late':
                    $lateDays++;
                    break;
            }

            // Calculate half day based on working hours
            if ($record->check_in && $record->check_out) {
                $checkIn = strtotime($record->check_in);
                $checkOut = strtotime($record->check_out);
                $workedHours = ($checkOut - $checkIn) / 3600;

                if ($workedHours < 4) {
                    $halfDays++;
                }
            }
        }

        // Calculate per day salary
        $perDaySalary = $workingDays > 0 ? $monthlySalary / $workingDays : 0;

        // Apply deduction rules
        $absentDeduction = $absentDays * $perDaySalary;
        $lateDeduction = $lateDays * ($perDaySalary * 0.25); // 25% deduction for late
        $halfDayDeduction = $halfDays * ($perDaySalary * 0.5); // 50% deduction for half day

        $totalDeduction = $absentDeduction + $lateDeduction + $halfDayDeduction;
        $finalSalary = $monthlySalary - $totalDeduction;

        return response()->json([
            'success' => true,
            'data' => [
                'basic_salary' => round($monthlySalary, 2),
                'working_days' => $workingDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'half_days' => $halfDays,
                'per_day_salary' => round($perDaySalary, 2),
                'absent_deduction' => round($absentDeduction, 2),
                'late_deduction' => round($lateDeduction, 2),
                'half_day_deduction' => round($halfDayDeduction, 2),
                'total_deduction' => round($totalDeduction, 2),
                'final_salary' => round($finalSalary, 2)
            ]
        ]);
    }

    // Apply deduction to payroll
    public function applyDeduction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|date_format:Y-m',
            'station_id' => 'required|exists:stations,id',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Calculate deduction
        $deductionResponse = $this->calculateDeduction(new Request([
            'employee_id' => $request->employee_id,
            'month' => $request->month,
            'station_id' => $request->station_id
        ]));

        $deductionData = json_decode($deductionResponse->getContent(), true);

        if (!$deductionData['success']) {
            return response()->json(['message' => 'Failed to calculate deduction'], 400);
        }

        // Check if payroll already exists
        $existing = DB::table('payroll')
            ->where('employee_id', $request->employee_id)
            ->where('payment_date', $request->payment_date)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Payroll already exists for this date'], 400);
        }

        // Create payroll record with deduction
        $id = DB::table('payroll')->insertGetId([
            'employee_id' => $request->employee_id,
            'station_id' => $request->station_id,
            'salary' => $deductionData['data']['final_salary'],
            'payment_date' => $request->payment_date,
            'note' => $request->note . " | Deduction Applied: -" . $deductionData['data']['total_deduction'],
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Payroll with deduction applied successfully',
            'data' => [
                'payroll_id' => $id,
                'deduction_details' => $deductionData['data']
            ]
        ], 201);
    }
}