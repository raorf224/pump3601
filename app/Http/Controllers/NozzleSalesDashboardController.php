<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NozzleSalesDashboardController extends Controller
{
    public function index()
    {
        return view('nozzle-sales');
    }

    public function getFilterOptions(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $stationsQuery = DB::table('stations')->select('id', 'name');

        if ($user->role === 'owner') {
            $stationsQuery->where('user_id', $user->id);
        } elseif ($user->role === 'employee') {
            $stationsQuery->where('id', $user->station_id);
        }

        $stations = $stationsQuery->get();
        $stationIds = $stations->pluck('id')->toArray();

        // Fetch Dispensers
        $dispensers = DB::table('dispensers')
            ->select('id', 'name', 'station_id')
            ->whereIn('station_id', $stationIds)
            ->get();

        // Fetch Nozzles with dispenser_id for cascading script
        $nozzles = DB::table('nozzles')
            ->join('dispensers', 'nozzles.dispenser_id', '=', 'dispensers.id')
            ->select('nozzles.id', 'nozzles.name', 'nozzles.dispenser_id', 'dispensers.station_id')
            ->whereIn('dispensers.station_id', $stationIds)
            ->get();

        return response()->json([
            'stations' => $stations,
            'dispensers' => $dispensers,
            'nozzles' => $nozzles,
        ]);
    }

    public function getDashboardData(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        // Direct join with nozzles.tank_id (Removing tank_dispenser to prevent duplicate rows)
        $query = DB::table('shift_nozzle_readings as snr')
            ->leftJoin('shifts as s', 'snr.shift_id', '=', 's.id')
            ->leftJoin('nozzles as n', 'snr.nozzle_id', '=', 'n.id')
            ->leftJoin('products as p', 'n.product_id', '=', 'p.id')
            ->leftJoin('dispensers as d', 'n.dispenser_id', '=', 'd.id')
            ->leftJoin('tanks as t', 'n.tank_id', '=', 't.id')
            ->leftJoin('users as u', 'snr.collected_from', '=', 'u.id')
            ->leftJoin('stations as st', 'd.station_id', '=', 'st.id');

        // Role Filter
        if ($user->role === 'owner') {
            $query->where('st.user_id', $user->id);
        } elseif ($user->role === 'employee') {
            $query->where('st.id', $user->station_id);
        }

        // Input Filters
        if ($request->filled('station_id')) {
            $query->where('st.id', $request->station_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween(DB::raw('DATE(s.start_time)'), [$request->date_from, $request->date_to]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('s.start_time', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('s.start_time', '<=', $request->date_to);
        }

        if ($request->filled('dispenser_ids')) {
            $dispenserIds = is_array($request->dispenser_ids) ? $request->dispenser_ids : explode(',', $request->dispenser_ids);
            $query->whereIn('d.id', array_filter($dispenserIds));
        }

        if ($request->filled('nozzle_ids')) {
            $nozzleIds = is_array($request->nozzle_ids) ? $request->nozzle_ids : explode(',', $request->nozzle_ids);
            $query->whereIn('snr.nozzle_id', array_filter($nozzleIds));
        }

        $kpiQuery = clone $query;
        $chartQuery = clone $query;

        // Fetch Data Table Records cleanly without duplicate tank mappings
        $data = $query->select([
            'snr.id',
            'snr.shift_id',
            't.name as tank_name',
            'd.name as dispenser_name',
            'p.name as product_name',
            'snr.nozzle_id',
            'n.name as nozzel_name',
            'snr.opening_reading',
            'snr.closing_reading',
            'snr.total_dispensed',
            'snr.testing_reading',
            'snr.rate',
            'snr.total_amount',
            'snr.collected_from',
            'u.username as collected_from_name',
            's.start_time as reading_date'
        ])
            ->orderBy('snr.id', 'desc')
            ->get();

        $totalSales = $kpiQuery->sum('snr.total_amount');
        $totalVolume = $kpiQuery->sum('snr.total_dispensed');
        $activeNozzlesCount = $kpiQuery->distinct('snr.nozzle_id')->count('snr.nozzle_id');

        $todayDate = date('Y-m-d');
        $yesterdayDate = date('Y-m-d', strtotime('-1 day'));

        $todaySalesQuery = DB::table('shift_nozzle_readings as snr')
            ->leftJoin('shifts as s', 'snr.shift_id', '=', 's.id')
            ->leftJoin('nozzles as n', 'snr.nozzle_id', '=', 'n.id')
            ->leftJoin('dispensers as d', 'n.dispenser_id', '=', 'd.id')
            ->leftJoin('stations as st', 'd.station_id', '=', 'st.id');

        if ($user->role === 'owner') {
            $todaySalesQuery->where('st.user_id', $user->id);
        } elseif ($user->role === 'employee') {
            $todaySalesQuery->where('st.id', $user->station_id);
        }

        if ($request->filled('station_id')) {
            $todaySalesQuery->where('st.id', $request->station_id);
        }

        $todaySales = (clone $todaySalesQuery)->whereDate('s.start_time', $todayDate)->sum('snr.total_amount');
        $lastDaySales = (clone $todaySalesQuery)->whereDate('s.start_time', $yesterdayDate)->sum('snr.total_amount');

        $trendData = (clone $chartQuery)
            ->select(
                DB::raw('DATE(s.start_time) as date'),
                DB::raw('SUM(snr.total_amount) as total_amount'),
                DB::raw('SUM(snr.total_dispensed) as total_volume')
            )
            ->groupBy(DB::raw('DATE(s.start_time)'))
            ->orderBy(DB::raw('DATE(s.start_time)'), 'asc')
            ->get();

        $nozzlePerformance = (clone $chartQuery)
            ->select(
                'n.name as nozzle_name',
                DB::raw('SUM(snr.total_amount) as total_amount')
            )
            ->groupBy('n.id', 'n.name')
            ->orderBy('total_amount', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'kpis' => [
                'total_sales' => number_format($totalSales, 2),
                'total_volume' => number_format($totalVolume, 2),
                'today_sales' => number_format($todaySales, 2),
                'last_day_sales' => number_format($lastDaySales, 2),
                'active_nozzles' => $activeNozzlesCount
            ],
            'summary' => [
                'raw_total_volume' => $totalVolume,
                'raw_total_sales' => $totalSales,
                'formatted_total_volume' => number_format($totalVolume, 2),
                'formatted_total_sales' => number_format($totalSales, 2),
            ],
            'charts' => [
                'trend' => $trendData,
                'nozzle_performance' => $nozzlePerformance
            ],
            'table_data' => $data
        ]);
    }
}