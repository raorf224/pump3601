<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PayrollManagementController extends Controller
{
    // ✅ Get all payroll runs - GROUPED by multi_employes_id
    public function index()
    {
        $payrolls = DB::table('payrol_management as pm')
            ->join('stations as s', 'pm.station_id', '=', 's.id')
            ->select(
                'pm.mutli_employes_id',
                'pm.station_id',
                'pm.title',
                'pm.frequency',
                DB::raw('SUM(pm.basic_pay) as total_basic_pay'),
                DB::raw('SUM(pm.net_pay) as total_net_pay'),
                DB::raw('SUM(pm.gross_pay) as total_gross_pay'),
                'pm.period_start',
                'pm.period_end',
                'pm.pay_date',
                'pm.status',
                DB::raw('MIN(pm.created_at) as created_at'),
                's.name as station_name',
                DB::raw('COUNT(DISTINCT pm.employe_id) as employee_count'),
                DB::raw('"No Component" as component_names')
            )
            ->groupBy(
                'pm.mutli_employes_id',
                'pm.station_id',
                'pm.title',
                'pm.frequency',
                'pm.period_start',
                'pm.period_end',
                'pm.pay_date',
                'pm.status',
                's.name'
            )
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $payrolls]);
    }
	
	public function index1(Request $request)
{
    $userId = $request->user_id; // Owner ID, pass from route or frontend

    $query = DB::table('payrol_management as pm')
        ->join('stations as s', 'pm.station_id', '=', 's.id')
        ->select(
            'pm.mutli_employes_id',
            'pm.station_id',
            'pm.title',
            'pm.frequency',
            DB::raw('SUM(pm.basic_pay) as total_basic_pay'),
            DB::raw('SUM(pm.net_pay) as total_net_pay'),
            DB::raw('SUM(pm.gross_pay) as total_gross_pay'),
            'pm.period_start',
            'pm.period_end',
            'pm.pay_date',
            'pm.status',
            DB::raw('MIN(pm.created_at) as created_at'),
            's.name as station_name',
            DB::raw('COUNT(DISTINCT pm.employe_id) as employee_count'),
            DB::raw('"No Component" as component_names')
        )
        ->when($userId, function($q) use ($userId) {
            return $q->where('s.user_id', $userId); // Filter only owner's stations
        })
        ->groupBy(
            'pm.mutli_employes_id',
            'pm.station_id',
            'pm.title',
            'pm.frequency',
            'pm.period_start',
            'pm.period_end',
            'pm.pay_date',
            'pm.status',
            's.name'
        )
        ->orderByDesc('created_at');

    $payrolls = $query->get();

    return response()->json(['data' => $payrolls]);
}

    // ✅ Create new payroll run - USING SAME API AS FRONTEND
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|integer|exists:stations,id',
            'title' => 'required|string|max:255',
            'frequency' => 'required|string|in:Daily,Weekly,Monthly',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'required|integer|exists:employees,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date',
            'pay_date' => 'required|date',
            'status' => 'required|string|in:Completed,Draft',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $payrolls = [];
            $multi_employes_id = rand(100000, 999999);

            // ✅ Get employee base salaries
            $employees = DB::table('employees as e')
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->select('e.id as employee_id', 'e.salary as base_salary', 'u.full_name')
                ->whereIn('e.id', $request->employee_ids)
                ->get()
                ->keyBy('employee_id');

            Log::info('🚀 STARTING PAYROLL CREATION', [
                'employee_ids' => $request->employee_ids,
                'employees_count' => $employees->count()
            ]);

            // ✅ Get assignments
            $assignments = $this->getAssignmentsFromDatabase();

            Log::info('📋 ASSIGNMENTS FROM DATABASE', [
                'total_assignments' => count($assignments),
                'sample_assignments' => array_slice($assignments, 0, 3)
            ]);

            // ✅ Create payroll for each employee
            foreach ($request->employee_ids as $employee_id) {
                $employee = $employees[$employee_id] ?? null;
                if (!$employee)
                    continue;

                $baseSalary = floatval($employee->base_salary);
                $totalEarnings = 0;
                $totalDeductions = 0;
                $attendanceDeduction = 0;
                $attendanceSummary = [];

                Log::info("👤 PROCESSING EMPLOYEE {$employee_id}", [
                    'name' => $employee->full_name,
                    'base_salary' => $baseSalary
                ]);

                // ✅ Find assignments for this employee
                $employeeAssignments = array_filter($assignments, function ($assignment) use ($employee_id) {
                    return isset($assignment['employee_id']) && intval($assignment['employee_id']) === intval($employee_id);
                });

                // ✅ Process salary components
                foreach ($employeeAssignments as $assignment) {
                    if (isset($assignment['components'])) {
                        $this->processComponent($assignment['components'], $baseSalary, $totalEarnings, $totalDeductions, $employee_id);
                    }
                }

                // ✅ Calculate attendance deductions
                $attendanceData = $this->calculateAttendanceDeduction(
                    $employee_id,
                    $request->station_id,
                    $request->pay_period_start,
                    $request->pay_period_end,
                    $baseSalary
                );

                $attendanceDeduction = $attendanceData['total_deduction'];
                $attendanceSummary = $attendanceData;

                Log::info("📊 ATTENDANCE DEDUCTION EMPLOYEE {$employee_id}", [
                    'attendance_deduction' => $attendanceDeduction,
                    'attendance_summary' => $attendanceSummary
                ]);

                // ✅ CALCULATE FINAL AMOUNTS (Include attendance deduction)
                $totalDeductions += $attendanceDeduction;
                $grossPay = $baseSalary + $totalEarnings;
                $netPayBeforeFrequency = $grossPay - $totalDeductions;
                $adjustedNetPay = $this->applyFrequencyAdjustment($netPayBeforeFrequency, $request->frequency);

                Log::info("💰 FINAL CALCULATION EMPLOYEE {$employee_id}", [
                    'base_salary' => $baseSalary,
                    'total_earnings' => $totalEarnings,
                    'component_deductions' => $totalDeductions - $attendanceDeduction,
                    'attendance_deductions' => $attendanceDeduction,
                    'total_deductions' => $totalDeductions,
                    'gross_pay' => $grossPay,
                    'net_pay' => $adjustedNetPay,
                    'components_applied' => ($totalEarnings > 0 || $totalDeductions > 0) ? 'YES' : 'NO'
                ]);

                // ✅ Create record with CALCULATED amounts
                $payrolls[] = [
                    'station_id' => $request->station_id,
                    'employe_id' => $employee_id,
                    'mutli_employes_id' => $multi_employes_id,
                    'title' => $request->title,
                    'frequency' => $request->frequency,
                    'basic_pay' => $baseSalary,
                    'net_pay' => $adjustedNetPay,
                    'gross_pay' => $grossPay,
                    'period_start' => $request->pay_period_start,
                    'period_end' => $request->pay_period_end,
                    'pay_date' => $request->pay_date,
                    'status' => $request->status,
                    'attendance_data' => json_encode($attendanceSummary), // Store attendance summary
                    'created_at' => now(),
                ];
            }

            DB::table('payrol_management')->insert($payrolls);
            DB::commit();

            Log::info('🎉 PAYROLL CREATED SUCCESSFULLY', [
                'total_records' => count($payrolls),
                'multi_employes_id' => $multi_employes_id
            ]);

            return response()->json(['message' => 'Payroll created successfully'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ FAILED TO CREATE PAYROLL: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create payroll: ' . $e->getMessage()], 500);
        }
    }

    // ✅ Calculate Attendance Deduction - FIXED 22 WORKING DAYS
    private function calculateAttendanceDeduction($employeeId, $stationId, $periodStart, $periodEnd, $monthlySalary)
    {
        try {
            // FIXED: Set working days to 22
            $workingDays = 22;

            // Get attendance records for the period
            $attendanceRecords = DB::table('attendance')
                ->where('employee_id', $employeeId)
                ->where('station_id', $stationId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->get();

            // Calculate deductions
            $absentDays = 0;
            $lateDays = 0;
            $halfDays = 0;

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

            // Calculate per day salary based on 22 working days
            $perDaySalary = $workingDays > 0 ? $monthlySalary / $workingDays : 0;

            // Apply deduction rules
            $absentDeduction = $absentDays * $perDaySalary;
            $lateDeduction = $lateDays * ($perDaySalary * 0.25); // 25% deduction for late
            $halfDayDeduction = $halfDays * ($perDaySalary * 0.5); // 50% deduction for half day

            $totalDeduction = $absentDeduction + $lateDeduction + $halfDayDeduction;

            return [
                'working_days' => $workingDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'half_days' => $halfDays,
                'per_day_salary' => round($perDaySalary, 2),
                'absent_deduction' => round($absentDeduction, 2),
                'late_deduction' => round($lateDeduction, 2),
                'half_day_deduction' => round($halfDayDeduction, 2),
                'total_deduction' => round($totalDeduction, 2)
            ];

        } catch (\Exception $e) {
            Log::error('❌ ATTENDANCE CALCULATION FAILED: ' . $e->getMessage());
            return [
                'working_days' => 22, // Fixed fallback
                'absent_days' => 0,
                'late_days' => 0,
                'half_days' => 0,
                'per_day_salary' => 0,
                'absent_deduction' => 0,
                'late_deduction' => 0,
                'half_day_deduction' => 0,
                'total_deduction' => 0
            ];
        }
    }

    // ✅ FIXED: Get assignments with CORRECT table names
    private function getAssignmentsFromDatabase()
    {
        try {
            $assignments = DB::table('employe_salary_management as esm')
            ->join('employees as e', function ($join) {
        $join->on('esm.emloye_id', '=', 'e.id')
             ->orOn('esm.emloye_id', '=', 'e.stationrow_id');
    })
                ->join('users as u', function ($join) {
                    $join->on('e.user_id', '=', 'u.id')
                        ->orOn('e.user_id', '=', 'u.stationrow_id');
                })
                
                 ->join('salary_componenet as sc', function ($join) {
                    $join->on('esm.component_id', '=', 'sc.id')
                        ->orOn('esm.component_id', '=', 'sc.stationrow_id');
                })
                ->join('stations as s', 'e.station_id', '=', 's.id')
                
                ->select(
                    'esm.emloye_id as employee_id',
                    'u.full_name as employee_name',
                    's.name as station_name',
                    'e.salary as employee_salary',
                    'esm.status',
                    'esm.created_at',
                    DB::raw('CONCAT(sc.component_name, " (", sc.type, " - ", sc.calculation, " - ", 
                    CASE 
                        WHEN sc.calculation = "Percentage" THEN CONCAT(sc.cal_ammount, "%")
                        ELSE CONCAT(sc.cal_ammount, "Rs.")
                    END, ")") as components')
                )
                ->where('esm.status', 'Active') // Employee salary management status
                ->where('sc.status', 'Active')  // ✅ ADDED: Salary component status check
                ->where('e.status', 'active')   // ✅ ADDED: Employee status check (optional)
                ->get()
                ->toArray();

            Log::info('✅ DATABASE ASSIGNMENTS LOADED', [
                'total_assignments' => count($assignments),
                'first_assignment' => $assignments[0] ?? 'None'
            ]);

            return array_map(function ($item) {
                return (array) $item;
            }, $assignments);

        } catch (\Exception $e) {
            Log::error('❌ DATABASE QUERY FAILED: ' . $e->getMessage());
            return [];
        }
    }

    // ✅ Process component assignment
    private function processComponent($componentsString, $baseSalary, &$totalEarnings, &$totalDeductions, $employee_id)
    {
        try {
            // Format: "Component Name (Type - Calculation - Amount)"
            preg_match('/^([^(]+) \(([^)]+)\)$/', $componentsString, $matches);

            if ($matches && count($matches) >= 3) {
                $componentName = trim($matches[1]);
                $details = $matches[2];

                // Split details: "Type - Calculation - Amount"
                $detailParts = explode(' - ', $details);

                if (count($detailParts) >= 3) {
                    $type = trim($detailParts[0]);
                    $calculation = trim($detailParts[1]);
                    $amountStr = trim($detailParts[2]);

                    // Extract numeric value from amount string
                    $amountValue = floatval(preg_replace('/[^\d.]/', '', $amountStr));

                    // Calculate final amount
                    $amount = $amountValue;
                    if ($calculation === 'Percentage') {
                        $amount = ($baseSalary * $amountValue) / 100;
                    }

                    if ($type === 'Earning') {
                        $totalEarnings += $amount;
                        Log::info("➕ EARNING COMPONENT APPLIED", [
                            'employee_id' => $employee_id,
                            'component' => $componentName,
                            'amount' => $amount,
                            'total_earnings' => $totalEarnings
                        ]);
                    } else if ($type === 'Deduction') {
                        $totalDeductions += $amount;
                        Log::info("➖ DEDUCTION COMPONENT APPLIED", [
                            'employee_id' => $employee_id,
                            'component' => $componentName,
                            'amount' => $amount,
                            'total_deductions' => $totalDeductions
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('❌ ERROR PROCESSING COMPONENT: ' . $e->getMessage());
        }
    }

    // ✅ Helper method for frequency adjustment
    private function applyFrequencyAdjustment($amount, $frequency)
    {
        switch ($frequency) {
            case 'Daily':
                return $amount / 30;
            case 'Weekly':
                return $amount / 5;
            case 'Monthly':
            default:
                return $amount;
        }
    }

    public function destroy($mutli_employes_id)
    {
        try {
            $payroll = DB::table('payrol_management')
                ->where('mutli_employes_id', $mutli_employes_id)
                ->first();

            if (!$payroll) {
                return response()->json(['error' => 'Payroll not found'], 404);
            }

            DB::table('payrol_management')
                ->where('mutli_employes_id', $mutli_employes_id)
                ->delete();

            return response()->json(['message' => 'Payroll deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete payroll: ' . $e->getMessage()], 500);
        }
    }

    public function getSummary()
    {
        $summary = DB::table('payrol_management')
            ->select(
                DB::raw('COUNT(*) as total_payrolls'),
                DB::raw('SUM(net_pay) as total_net_pay'),
                DB::raw('SUM(gross_pay) as total_gross_pay'),
                DB::raw('COUNT(CASE WHEN status = "Completed" THEN 1 END) as completed_payrolls'),
                DB::raw('COUNT(CASE WHEN status = "Draft" THEN 1 END) as draft_payrolls')
            )
            ->first();

        return response()->json(['data' => $summary]);
    }

    // ✅ API endpoint for attendance deduction calculation
    public function calculateAttendanceDeductionApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'station_id' => 'required|exists:stations,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'monthly_salary' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deductionData = $this->calculateAttendanceDeduction(
                $request->employee_id,
                $request->station_id,
                $request->period_start,
                $request->period_end,
                $request->monthly_salary
            );

            return response()->json([
                'success' => true,
                'data' => $deductionData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate attendance deduction',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}