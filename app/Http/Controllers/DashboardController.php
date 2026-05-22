<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index($any = null)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        // ✅ Apply permission logic only if role = employee
        if ($user->role === 'employee') {
            $role = DB::select("
                SELECT r.id 
                FROM employees e
                JOIN roles r 
                    ON e.role COLLATE utf8mb4_unicode_ci = r.name COLLATE utf8mb4_unicode_ci
                WHERE e.user_id = ?
                LIMIT 1
            ", [$user->id]);

            $roleId = $role[0]->id ?? null;

            if (!$roleId) {
                abort(403, 'Role not found.');
            }

            // ✅ Get all permissions for this role
            $permissions = DB::table('role_has_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->where('role_has_permissions.role_id', $roleId)
                ->pluck('permissions.name')
                ->toArray();

            // ✅ Map route → permission
            $pagePermissions = [
                'users' => 'users',
                'station-sites' => 'sites',
                'products-setup' => 'product',
                'tanks-visualization' => 'tanks',
                'dispenser-visualization' => 'dispenser',
                'nozzel-visualization' => 'nozzels',
                'oil-purchase' => 'oilpurchase',
                'accounts' => 'accounts',
                'transactions' => 'transactions',
                'employe' => 'employees',
                'payroll' => 'employee_payroll',
                'shifts' => 'shifts',
                'attendance' => 'attendance',
                'account-statement' => 'account_report',
                'expense-sheet' => 'expense_sheet',
                'store-setup' => 'store_setup',
                'store-products' => 'product',
                'pos' => 'pos',
                'permissions' => 'permissions',
                'lube-purchase' => 'lubricants',
				'site_amount_workflow' => 'bank',
				'salary_component' => 'salary_component',
				'employe_salary_management' => 'employe_salary_management',
				'payrol_run_management' => 'payrol_run_management',
				'payslip' => 'payslip',
				'close' => 'close',
            ];

            // ✅ Check if page is permission protected
            if (isset($pagePermissions[$any])) {
                if (!in_array($pagePermissions[$any], $permissions)) {
                    abort(403, 'You do not have permission to access this page.');
                }

                // ✅ If allowed, load that view
                return view($any);
            } else {
                abort(404, 'Page not found.');
            }
        }

        // ✅ For non-employee roles — allow full access
        if (view()->exists($any)) {
            return view($any);
        }

        abort(404, 'Page not found.');
    }
}