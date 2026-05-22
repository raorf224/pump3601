<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

// Import all the necessary models
use App\Models\Transaction;
use App\Models\OilPurchase;
use App\Models\LubeDocument;
use App\Models\Tank;
use App\Models\Dispenser;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Account;
use App\Models\Station;
use App\Models\LubeLine;


class DashController extends Controller
{
      public function index()
    {
        try {
            // === Card 1: Overview of Last Month (Current Debit/Credit) ===
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $monthlyCredit = Transaction::where('type', 'income')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->sum('credit');

            $monthlyDebit = Transaction::where('type', 'expense')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->sum('debit');

            // Data for the graph (last 30 days)
            $dailyTransactions = Transaction::where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at')
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('d M'); // group by day
                });

            $transactionLabels = [];
            $creditData = [];
            $debitData = [];

            foreach ($dailyTransactions as $label => $transactions) {
                $transactionLabels[] = $label;
                $creditData[] = $transactions->where('type', 'income')->sum('credit');
                $debitData[] = $transactions->where('type', 'expense')->sum('debit');
            }

            // === Card 2: Order History (Combined Purchases) ===
            $oilPurchases = OilPurchase::with(['supplier', 'station', 'tank'])
                ->latest()
                ->take(3)
                ->get();

            $lubePurchases = LubeDocument::with(['station', 'account', 'lines.product'])
                ->where('doc_type', 'purchase')
                ->latest()
                ->take(3)
                ->get();

            // === Card 3: Stat Cards ===
            $totalTanks = Tank::count();
            $totalDispensers = Dispenser::count();
            $totalNozzles = $totalDispensers; // Placeholder as there is no nozzle table
            $fuelProductsCount = Product::where('category', 'fuel')->count();
            $lubricantProductsCount = Product::where('category', 'lubricants')->count();
            $totalEmployees = Employee::count();

            // === Card 4: Live Supplier Tracking ===
            $suppliers = Account::where('type', 'supplier')->latest()->get();

            // === Other data points for the dashboard ===
            $stations = Station::all();
            $employees = Employee::with('user')->get(); // Eager load user to prevent N+1 issues

            // API routes should return JSON data
            return response()->json([
                'monthlyCredit' => $monthlyCredit,
                'monthlyDebit' => $monthlyDebit,
                'transactionGraph' => [
                    'labels' => $transactionLabels,
                    'creditData' => $creditData,
                    'debitData' => $debitData,
                ],
                'purchaseHistory' => [
                    'oil' => $oilPurchases,
                    'lube' => $lubePurchases
                ],
                'stats' => [
                    'tanks' => $totalTanks,
                    'dispensers' => $totalDispensers,
                    'nozzles' => $totalNozzles,
                    'fuelProducts' => $fuelProductsCount,
                    'lubeProducts' => $lubricantProductsCount,
                    'employees' => $totalEmployees,
                ],
                'suppliers' => $suppliers,
                'stations' => $stations,
                'employeesList' => $employees,
            ]);

        } catch (\Exception $e) {
            // Return a JSON response with the error
            return response()->json(['error' => 'An error occurred while fetching dashboard data.', 'message' => $e->getMessage()], 500);
        }
    }
}
