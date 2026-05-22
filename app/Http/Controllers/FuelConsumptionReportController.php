<?php
// app/Http/Controllers/FuelConsumptionReportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuelConsumptionReportController extends Controller
{
    public function index()
    {
        return view('reports.fuel-consumption');
    }

    public function getData(Request $request)
{
    $startDate = $request->get('start_date', date('Y-m-01'));
    $endDate = $request->get('end_date', date('Y-m-t'));

    $stationId = $request->get('station_id');
    $tankId = $request->get('tank_id');

    /*
    |-----------------------------
    | MAIN QUERY
    |-----------------------------
    */
    $query = DB::table('fuel_layer_consumptions as flc')

        ->join('fuel_inventory_layers as fil', 'flc.layer_id', '=', 'fil.id')
        ->join('tanks as t', 'fil.tank_id', '=', 't.id')
        ->join('stations as s', 't.station_id', '=', 's.id')

        ->whereBetween('flc.created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);

    if ($stationId) {
        $query->where('s.id', $stationId);
    }

    if ($tankId) {
        $query->where('t.id', $tankId);
    }

    $records = $query->select(
        'flc.id',
        'flc.layer_id',
        'flc.sale_id',
        'flc.qty',
        'flc.cost_rate',
        'flc.sale_rate',
        'flc.cost_amount',
        'flc.sale_amount',
        'flc.profit',
        DB::raw('DATE(flc.created_at) as created_at'),
        't.id as tank_id',
        't.name as tank_name',
        's.id as station_id',
        's.name as station_name'
    )
    ->orderBy('flc.created_at', 'desc')
    ->get();

    /*
    |-----------------------------
    | TOTALS
    |-----------------------------
    */
    $totals = DB::table('fuel_layer_consumptions as flc')
        ->join('fuel_inventory_layers as fil', 'flc.layer_id', '=', 'fil.id')
        ->join('tanks as t', 'fil.tank_id', '=', 't.id')
        ->join('stations as s', 't.station_id', '=', 's.id')
        ->whereBetween('flc.created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);

    if ($stationId) {
        $totals->where('s.id', $stationId);
    }

    if ($tankId) {
        $totals->where('t.id', $tankId);
    }

    $totals = $totals->select(
        DB::raw('COALESCE(SUM(qty),0) as total_qty'),
        DB::raw('COALESCE(SUM(cost_amount),0) as total_cost'),
        DB::raw('COALESCE(SUM(sale_amount),0) as total_sale'),
        DB::raw('COALESCE(SUM(CASE WHEN profit > 0 THEN profit ELSE 0 END),0) as total_profit'),
        DB::raw('COALESCE(ABS(SUM(CASE WHEN profit < 0 THEN profit ELSE 0 END)),0) as total_loss')
    )->first();

    /*
    |-----------------------------
    | CHART DATA
    |-----------------------------
    */
    $daily = DB::table('fuel_layer_consumptions as flc')
        ->join('fuel_inventory_layers as fil', 'flc.layer_id', '=', 'fil.id')
        ->join('tanks as t', 'fil.tank_id', '=', 't.id')
        ->join('stations as s', 't.station_id', '=', 's.id')
        ->whereBetween('flc.created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);

    if ($stationId) {
        $daily->where('s.id', $stationId);
    }

    if ($tankId) {
        $daily->where('t.id', $tankId);
    }

    $daily = $daily->select(
        DB::raw('DATE(flc.created_at) as date'),
        DB::raw('COALESCE(SUM(CASE WHEN profit > 0 THEN profit ELSE 0 END),0) as profit'),
        DB::raw('COALESCE(SUM(CASE WHEN profit < 0 THEN profit ELSE 0 END),0) as loss')
    )
    ->groupBy(DB::raw('DATE(flc.created_at)'))
    ->orderBy('date')
    ->get();

    $dates = [];
    $profits = [];
    $losses = [];

    $map = [];
    foreach ($daily as $d) {
        $map[$d->date] = [
            'p' => $d->profit,
            'l' => abs($d->loss)
        ];
    }

    $cur = strtotime($startDate);
    $end = strtotime($endDate);

    while ($cur <= $end) {
        $d = date('Y-m-d', $cur);
        $dates[] = $d;

        $profits[] = $map[$d]['p'] ?? 0;
        $losses[] = $map[$d]['l'] ?? 0;

        $cur = strtotime('+1 day', $cur);
    }

    return response()->json([
        'records' => $records,
        'total_qty' => $totals->total_qty,
        'total_cost' => $totals->total_cost,
        'total_sale' => $totals->total_sale,
        'total_profit' => $totals->total_profit,
        'total_loss' => $totals->total_loss,

        'chart_dates' => $dates,
        'chart_profits' => $profits,
        'chart_losses' => $losses
    ]);
}
}