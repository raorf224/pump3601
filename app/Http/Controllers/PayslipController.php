<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayrollManagement;
use App\Models\Employee;
use App\Models\Station;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalaryManagement;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollManagement::with(['employee.user', 'station']);
        
        // Apply filters
        if ($request->has('station_id') && $request->station_id) {
            $query->where('station_id', $request->station_id);
        }
        
        if ($request->has('employees') && $request->employees) {
            $employeeIds = is_array($request->employees) ? $request->employees : explode(',', $request->employees);
            $query->whereIn('employe_id', $employeeIds);
        }
        
        if ($request->has('pay_from') && $request->pay_from) {
            $query->where('period_start', '>=', $request->pay_from);
        }
        
        if ($request->has('pay_to') && $request->pay_to) {
            $query->where('period_end', '<=', $request->pay_to);
        }
        
        // Role-based access control
        $user = auth()->user();
        if ($user && $user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $query->where('employe_id', $employee->id);
            }
        } elseif ($user && $user->role === 'manager') {
            $stations = Station::where('user_id', $user->id)->pluck('id');
            $query->whereIn('station_id', $stations);
        }
        
        // For AJAX requests (DataTables)
        if ($request->ajax()) {
            $payslips = $query->get();
            
            $data = [];
            foreach ($payslips as $index => $payslip) {
                $data[] = [
                    'DT_RowIndex' => $index + 1,
                    'id' => $payslip->id,
                    'station_name' => $payslip->station->name ?? 'N/A',
                    'employee_name' => $payslip->employee->user->full_name ?? 'N/A',
                    'role' => $payslip->employee ? ucfirst(str_replace('_', ' ', $payslip->employee->role)) : 'N/A',
                    'pay_period' => $payslip->period_start . ' to ' . $payslip->period_end,
                    'pay_date' => $payslip->pay_date,
                    'net_pay' => $payslip->net_pay,
                    'net_pay_formatted' => 'Rs. ' . number_format($payslip->net_pay, 2),
                    'status' => $payslip->status,
                    'action' => '
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary view-payslip" data-id="'.$payslip->id.'" title="View Payslip">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success download-payslip" data-id="'.$payslip->id.'" title="Download PDF">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    '
                ];
            }
            
            return response()->json([
                'data' => $data,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data)
            ]);
        }
        
        // For non-AJAX requests, return view
        return view('your-payslip-view');
    }
    
    public function show($id)
    {
        $payslip = PayrollManagement::with(['employee.user', 'station'])
            ->findOrFail($id);
            
        // Get salary components
        $employeeComponents = EmployeeSalaryManagement::where('emloye_id', $payslip->employe_id)
            ->with('component')
            ->get();
            
        $earnings = [];
        $deductions = [];
        
        foreach ($employeeComponents as $empComponent) {
            if ($empComponent->component) {
                $component = $empComponent->component;
                $amount = $this->calculateComponentAmount($component, $payslip->basic_pay);
                
                if ($component->type === 'Earning') {
                    $earnings[] = [
                        'component_name' => $component->component_name,
                        'amount' => $amount
                    ];
                } else {
                    $deductions[] = [
                        'component_name' => $component->component_name,
                        'amount' => $amount
                    ];
                }
            }
        }
        
        // Parse attendance data - FIXED to match your JSON structure
        $attendanceData = json_decode($payslip->attendance_data, true) ?? [];
        
        // Calculate present days from your JSON structure
        $workingDays = $attendanceData['working_days'] ?? 0;
        $absentDays = $attendanceData['absent_days'] ?? 0;
        $presentDays = $workingDays - $absentDays;
        
        return response()->json([
            'data' => [
                'id' => $payslip->id,
                'employee_name' => $payslip->employee->user->full_name ?? 'N/A',
                'employe_id' => $payslip->employe_id,
                'role' => $payslip->employee->role ?? 'N/A',
                'station_name' => $payslip->station->name ?? 'N/A',
                'period_start' => $payslip->period_start,
                'period_end' => $payslip->period_end,
                'pay_date' => $payslip->pay_date,
                'basic_pay' => $payslip->basic_pay,
                'gross_pay' => $payslip->gross_pay,
                'net_pay' => $payslip->net_pay,
                'status' => $payslip->status,
                'working_days' => $workingDays,
                'present_days' => $presentDays,
                'paid_leave' => $attendanceData['paid_leave'] ?? 0,
                'unpaid_leave' => $attendanceData['unpaid_leave'] ?? 0,
                'half_days' => $attendanceData['half_days'] ?? 0,
                'absent_days' => $absentDays,
                'late_days' => $attendanceData['late_days'] ?? 0,
                'per_day_salary' => $attendanceData['per_day_salary'] ?? 0,
                'absent_deduction' => $attendanceData['absent_deduction'] ?? 0,
                'late_deduction' => $attendanceData['late_deduction'] ?? 0,
                'half_day_deduction' => $attendanceData['half_day_deduction'] ?? 0,
                'total_deduction' => $attendanceData['total_deduction'] ?? 0,
                'earnings' => $earnings,
                'deductions' => $deductions
            ]
        ]);
    }
    
    private function calculateComponentAmount($component, $basicPay)
    {
        if ($component->calculation === 'Percentage') {
            return ($basicPay * $component->cal_ammount) / 100;
        }
        
        return $component->cal_ammount;
    }
}