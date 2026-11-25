<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StationTankSalesController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sap' => ['required', 'string', 'max:45'],
            'tank_id' => ['nullable', 'integer'],
            'erpcode' => ['nullable', 'string', 'max:45'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $stations = DB::table('stations')
            ->where('sap', trim($validated['sap']))
            ->where('status', 1)
            ->get();

        if ($stations->isEmpty()) {
            return response()->json(['message' => 'No active station found for the supplied SAP code.'], 404);
        }

        if ($stations->count() > 1) {
            return response()->json(['message' => 'SAP code is assigned to more than one station.'], 409);
        }

        $station = $stations->first();
        [$from, $to] = $this->dateRange($validated);
        $isLocal = (int) $station->local === 1;

        $tanks = DB::table('tanks as t')
            ->leftJoin('products as p', 'p.id', '=', 't.product_id')
            ->where('t.station_id', $station->id)
            ->where('t.status', 'active')
            ->select(
                't.id',
                't.stationrow_id',
                't.name',
                't.erpcode',
                't.capacity',
                't.current_level',
                't.current_level_mm',
                't.product_id',
                'p.name as product_name'
            )
            ->orderBy('t.id')
            ->get();

        $response = [
            'station' => [
                'id' => (int) $station->id,
                'sap' => $station->sap,
                'name' => $station->name,
                'phone' => $station->phone,
                'location' => $station->location,
                'city' => $station->city,
                'lat' => $station->lat,
                'lng' => $station->lng,
            ],
            'filters' => [
                'tank_id' => isset($validated['tank_id']) ? (int) $validated['tank_id'] : null,
                'erpcode' => isset($validated['erpcode']) && trim((string) $validated['erpcode']) !== '' ? trim((string) $validated['erpcode']) : null,
                'from' => $from?->toDateTimeString(),
                'to' => $to?->toDateTimeString(),
            ],
            'tanks' => $tanks->map(fn ($tank) => [
                'id' => (int) $tank->id,
                'name' => $tank->name,
                'erpcode' => $tank->erpcode !== null && $tank->erpcode !== '' ? $tank->erpcode : null,
                'product_id' => $tank->product_id !== null ? (int) $tank->product_id : null,
                'product_name' => $tank->product_name,
                'capacity' => (float) $tank->capacity,
                'current_level' => (float) $tank->current_level,
                'current_level_mm' => (float) $tank->current_level_mm,
            ])->values(),
            'sales' => null,
        ];

        $tank = null;
        $tankId = null;
        if (isset($validated['tank_id'])) {
            $tankId = (int) $validated['tank_id'];
            $tank = $tanks->firstWhere('id', $tankId);
            if (!$tank) {
                return response()->json(['message' => 'Selected tank does not belong to this station.'], 404);
            }
        } elseif (!empty($validated['erpcode'])) {
            $erpcode = trim((string) $validated['erpcode']);
            $tank = $tanks->first(function ($t) use ($erpcode) {
                return $t->erpcode !== null && strcasecmp((string) $t->erpcode, $erpcode) === 0;
            });
            if (!$tank) {
                return response()->json(['message' => 'Selected tank does not belong to this station.'], 404);
            }
            $tankId = (int) $tank->id;
        }

        if ($tank === null || $tankId === null) {
            return response()->json($response);
        }

        $baseQuery = $this->salesQuery((int) $station->id, $tankId, $isLocal, $from, $to);
        $summary = (clone $baseQuery)->selectRaw('
            COUNT(DISTINCT snr.id) as reading_count,
            COUNT(DISTINCT n.id) as nozzle_count,
            COALESCE(SUM(snr.total_dispensed), 0) as total_liters,
            COALESCE(SUM(COALESCE(snr.total_amount, snr.total_dispensed * snr.rate)), 0) as total_amount,
            MIN(s.start_time) as first_shift_time,
            MAX(COALESCE(s.end_time, s.start_time)) as last_shift_time
        ')->first();

        $nozzles = (clone $baseQuery)
            ->groupBy('n.id', 'n.name')
            ->selectRaw('
                n.id as nozzle_id,
                n.name as nozzle_name,
                COUNT(DISTINCT snr.id) as reading_count,
                COALESCE(SUM(snr.total_dispensed), 0) as total_liters,
                COALESCE(SUM(COALESCE(snr.total_amount, snr.total_dispensed * snr.rate)), 0) as total_amount
            ')
            ->orderBy('n.name')
            ->get()
            ->map(fn ($nozzle) => [
                'nozzle_id' => (int) $nozzle->nozzle_id,
                'nozzle_name' => $nozzle->nozzle_name,
                'reading_count' => (int) $nozzle->reading_count,
                'total_liters' => round((float) $nozzle->total_liters, 2),
                'total_amount' => round((float) $nozzle->total_amount, 2),
            ])->values();

        $response['sales'] = [
            'tank' => [
                'id' => (int) $tank->id,
                'name' => $tank->name,
                'erpcode' => $tank->erpcode !== null && $tank->erpcode !== '' ? $tank->erpcode : null,
                'product_id' => $tank->product_id !== null ? (int) $tank->product_id : null,
                'product_name' => $tank->product_name,
            ],
            'nozzle_count' => (int) $summary->nozzle_count,
            'reading_count' => (int) $summary->reading_count,
            'total_liters' => round((float) $summary->total_liters, 2),
            'total_amount' => round((float) $summary->total_amount, 2),
            'data_from' => $from?->toDateTimeString() ?? $summary->first_shift_time,
            'data_to' => $to?->toDateTimeString() ?? $summary->last_shift_time,
            'first_shift_time' => $summary->first_shift_time,
            'last_shift_time' => $summary->last_shift_time,
            'nozzles' => $nozzles,
        ];

        return response()->json($response);
    }

    public function updatePushStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sap' => ['nullable', 'string', 'max:45'],
            'sap_code' => ['nullable', 'string', 'max:45'],
            'tank_id' => ['nullable', 'integer'],
            'erpcode' => ['nullable', 'string', 'max:45'],
            'tank_erp' => ['nullable', 'string', 'max:45'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $sap = trim((string) ($validated['sap_code'] ?? $validated['sap'] ?? ''));
        if ($sap === '') {
            return response()->json(['message' => 'sap_code is required.'], 422);
        }

        $stations = DB::table('stations')
            ->where('sap', $sap)
            ->where('status', 1)
            ->get();

        if ($stations->isEmpty()) {
            return response()->json(['message' => 'No active station found for the supplied SAP code.'], 404);
        }

        if ($stations->count() > 1) {
            return response()->json(['message' => 'SAP code is assigned to more than one station.'], 409);
        }

        $station = $stations->first();
        [$from, $to] = $this->dateRange($validated);
        $isLocal = (int) $station->local === 1;

        $tanks = DB::table('tanks as t')
            ->where('t.station_id', $station->id)
            ->where('t.status', 'active')
            ->select('t.id', 't.stationrow_id', 't.name', 't.erpcode', 't.product_id')
            ->orderBy('t.id')
            ->get();

        $tank = null;
        $tankId = null;
        if (isset($validated['tank_id'])) {
            $tankId = (int) $validated['tank_id'];
            $tank = $tanks->firstWhere('id', $tankId);
            if (!$tank) {
                return response()->json(['message' => 'Selected tank does not belong to this station.'], 404);
            }
        } else {
            $erpcode = trim((string) ($validated['tank_erp'] ?? $validated['erpcode'] ?? ''));
            if ($erpcode === '') {
                return response()->json(['message' => 'tank_id or tank_erp/erpcode is required.'], 422);
            }
            $tank = $tanks->first(function ($t) use ($erpcode) {
                return $t->erpcode !== null && strcasecmp((string) $t->erpcode, $erpcode) === 0;
            });
            if (!$tank) {
                return response()->json(['message' => 'Selected tank does not belong to this station.'], 404);
            }
            $tankId = (int) $tank->id;
        }

        $shiftJoin = $isLocal ? 's.stationrow_id' : 's.id';
        $nozzleJoin = $isLocal ? 'n.stationrow_id' : 'n.id';
        $tankJoin = $isLocal ? 't.stationrow_id' : 't.id';
        $fromStr = $from->toDateTimeString();
        $toStr = $to->toDateTimeString();

        $nozzleCount = DB::table('nozzles as n')
            ->join('tanks as t', 'n.tank_id', '=', $tankJoin)
            ->where('n.station_id', $station->id)
            ->where('t.id', $tankId)
            ->distinct()
            ->count('n.id');

        $result = DB::transaction(function () use ($shiftJoin, $nozzleJoin, $tankJoin, $fromStr, $toStr, $station, $tankId) {
            $affected = DB::update(
                "UPDATE shift_nozzle_readings snr
                 JOIN shifts s ON snr.shift_id = {$shiftJoin}
                 JOIN nozzles n ON snr.nozzle_id = {$nozzleJoin}
                 JOIN tanks t ON n.tank_id = {$tankJoin}
                 SET snr.is_pushed = CASE
                        WHEN s.start_time >= ? AND s.start_time <= ? THEN 1
                        ELSE 0
                     END,
                     snr.updated_at = NOW()
                 WHERE s.station_id = ?
                   AND t.id = ?",
                [$fromStr, $toStr, $station->id, $tankId]
            );

            $counts = DB::table('shift_nozzle_readings as snr')
                ->join('shifts as s', "snr.shift_id", '=', $shiftJoin)
                ->join('nozzles as n', "snr.nozzle_id", '=', $nozzleJoin)
                ->join('tanks as t', 'n.tank_id', '=', $tankJoin)
                ->where('s.station_id', $station->id)
                ->where('t.id', $tankId)
                ->selectRaw('COUNT(*) as total_records')
                ->selectRaw("SUM(CASE WHEN snr.is_pushed = 1 THEN 1 ELSE 0 END) as pushed_records")
                ->selectRaw("SUM(CASE WHEN snr.is_pushed = 0 OR snr.is_pushed IS NULL THEN 1 ELSE 0 END) as not_pushed_records")
                ->first();

            return [
                'affected_rows' => $affected,
                'total_records' => (int) ($counts->total_records ?? 0),
                'pushed_records' => (int) ($counts->pushed_records ?? 0),
                'not_pushed_records' => (int) ($counts->not_pushed_records ?? 0),
            ];
        });

        return response()->json([
            'message' => 'Nozzle readings push status updated successfully.',
            'station' => [
                'id' => (int) $station->id,
                'sap' => $station->sap,
                'name' => $station->name,
            ],
            'tank' => [
                'id' => (int) $tank->id,
                'name' => $tank->name,
                'erpcode' => $tank->erpcode !== null && $tank->erpcode !== '' ? $tank->erpcode : null,
            ],
            'filters' => [
                'from' => $from->toDateTimeString(),
                'to' => $to->toDateTimeString(),
            ],
            'nozzle_count' => $nozzleCount,
            'affected_rows' => $result['affected_rows'],
            'total_records' => $result['total_records'],
            'pushed_records' => $result['pushed_records'],
            'not_pushed_records' => $result['not_pushed_records'],
        ]);
    }

    private function salesQuery(int $stationId, int $tankId, bool $isLocal, ?Carbon $from, ?Carbon $to)
    {
        $query = DB::table('shift_nozzle_readings as snr')
            ->join('shifts as s', 'snr.shift_id', '=', $isLocal ? 's.stationrow_id' : 's.id')
            ->join('nozzles as n', 'snr.nozzle_id', '=', $isLocal ? 'n.stationrow_id' : 'n.id')
            ->join('tanks as t', 'n.tank_id', '=', $isLocal ? 't.stationrow_id' : 't.id')
            ->where('s.station_id', $stationId)
            ->where('t.id', $tankId)
            ->whereRaw('IFNULL(snr.is_pushed, 0) <> 1');

        if ($from) {
            $query->where('s.start_time', '>=', $from->toDateTimeString());
        }
        if ($to) {
            $query->where('s.start_time', '<=', $to->toDateTimeString());
        }

        return $query;
    }

    private function dateRange(array $validated): array
    {
        $from = !empty($validated['from']) ? $this->filterBoundary($validated['from'], false) : null;
        $to = !empty($validated['to'])
            ? $this->filterBoundary($validated['to'], true)
            : ($from ? now() : null);

        return [$from, $to];
    }

    private function filterBoundary(string $value, bool $end): Carbon
    {
        $date = Carbon::parse($value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $end ? $date->endOfDay() : $date->startOfDay();
        }
        return $date;
    }
}
