<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OverviewController extends Controller
{
    /**
     * Display the Shift Overview Dashboard
     */
    public function index()
    {
        return view('shift-overview');
    }

    /**
     * API: Get ALL stations with complete stats
     * (Frontend will filter based on role)
     */
    public function getStationsOverview(Request $request)
    {
        // Direct Single SQL Query to fetch station details along with exact Counts
        $stations = DB::select("
        SELECT 
            s.id,
            s.name,
            s.location,
            s.city,
            s.phone,
            s.status,
            s.created_at,
            s.user_id,
            u.full_name AS owner_name,
            u.email AS owner_email,
            u.phone AS owner_phone,
            COUNT(DISTINCT t.id) AS tanks_count,
            COUNT(DISTINCT d.id) AS dispensers_count,
            COUNT(DISTINCT n.id) AS nozzles_count
        FROM stations s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN tanks t ON t.station_id = s.id
        LEFT JOIN dispensers d ON d.station_id = s.id
        LEFT JOIN nozzles n ON n.dispenser_id = d.id
        GROUP BY 
            s.id, s.name, s.location, s.city, s.phone, 
            s.status, s.created_at, s.user_id, u.full_name, 
            u.email, u.phone
        ORDER BY s.created_at DESC
    ");

        foreach ($stations as $station) {

            // Typecast counts to integers
            $station->tanks_count = (int) $station->tanks_count;
            $station->dispensers_count = (int) $station->dispensers_count;
            $station->nozzles_count = (int) $station->nozzles_count;

            // Exact Setup Done condition
            $station->initial_setup_done = (
                $station->tanks_count > 0 &&
                $station->dispensers_count > 0 &&
                $station->nozzles_count > 0
            );

            // Shifts stats
            $station->total_shifts = DB::table('shifts')
                ->where('station_id', $station->id)
                ->count();

            $station->closed_shifts = DB::table('shifts')
                ->where('station_id', $station->id)
                ->where('status', 'closed')
                ->count();

            $station->open_shifts = DB::table('shifts')
                ->where('station_id', $station->id)
                ->where('status', 'open')
                ->count();

            // Last shift info
            $lastShift = DB::table('shifts')
                ->where('station_id', $station->id)
                ->orderBy('start_time', 'desc')
                ->first();

            if ($lastShift) {
                $station->last_shift_date = $lastShift->start_time;
                $station->last_shift_status = $lastShift->status;

                $daysSinceLastShift = abs((int) now()->diffInDays(\Carbon\Carbon::parse($lastShift->start_time)));
                $station->days_since_last_shift = $daysSinceLastShift;

                if ($daysSinceLastShift > 3) {
                    $station->flag = 'red';
                } elseif ($daysSinceLastShift > 1) {
                    $station->flag = 'yellow';
                } else {
                    $station->flag = 'green';
                }
            } else {
                $station->last_shift_date = null;
                $station->last_shift_status = null;
                $station->days_since_last_shift = null;
                $station->flag = 'gray';
            }

            // Manager info
            $manager = DB::select("
            SELECT 
                e.id,
                e.role,
                e.address,
                e.city,
                e.cnic,
                e.phone AS emp_phone,
                e.salary,
                e.status AS emp_status,
                u.full_name AS manager_name,
                u.email AS manager_email,
                u.phone AS user_phone
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id
            WHERE e.station_id = ? AND e.role = 'manager'
            LIMIT 1
        ", [$station->id]);

            $station->manager = $manager[0] ?? null;

            // Clean Integer Age
            $station->age_days = abs((int) now()->diffInDays(\Carbon\Carbon::parse($station->created_at)));
        }

        return response()->json($stations);
    }

    /**
     * API: Get shifts list for a specific station (for modal)
     */
    public function getStationShifts($stationId)
    {
        $shifts = DB::select("
            SELECT 
                s.id,
                s.shift_no,
                s.start_time,
                s.end_time,
                s.cash_handover,
                s.cash_return,
                s.status,
                s.created_at,
                u.full_name AS incharger_name,
                e.role AS incharger_role,
                (SELECT COUNT(*) FROM shift_nozzle_readings snr WHERE snr.shift_id = s.id) AS readings_count,
                (SELECT IFNULL(SUM(snr.total_amount), 0) FROM shift_nozzle_readings snr WHERE snr.shift_id = s.id) AS total_sale
            FROM shifts s
            LEFT JOIN employees e ON s.shift_incharger = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE s.station_id = ?
            ORDER BY s.start_time DESC
            LIMIT 100
        ", [$stationId]);

        return response()->json($shifts);
    }

    /**
     * API: Get manager full details for a station
     */
    public function getStationManager($stationId)
    {
        $manager = DB::select("
            SELECT 
                e.id,
                e.role,
                e.address,
                e.city,
                e.region,
                e.country,
                e.cnic,
                e.phone AS emp_phone,
                e.salary,
                e.status AS emp_status,
                e.created_at AS emp_created,
                u.id AS user_id,
                u.full_name,
                u.username,
                u.email,
                u.phone AS user_phone,
                u.status AS user_status
            FROM employees e
            LEFT JOIN users u ON e.user_id = u.id
            WHERE e.station_id = ? AND e.role = 'manager'
            LIMIT 1
        ", [$stationId]);

        if (empty($manager)) {
            return response()->json([
                'success' => false,
                'message' => 'No manager assigned to this station'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $manager[0]
        ]);
    }

    /**
     * API: Get all employees for the current user's role 
     * (to filter stations for employee role)
     */
    public function getEmployeeStations(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json([]);
        }

        // Employee ke saare stations nikaalo
        $stations = DB::select("
            SELECT DISTINCT s.id
            FROM stations s
            LEFT JOIN employees e ON e.station_id = s.id
            LEFT JOIN users u ON u.station_id = s.id
            WHERE e.user_id = ? OR u.id = ?
        ", [$userId, $userId]);

        return response()->json($stations);
    }
}