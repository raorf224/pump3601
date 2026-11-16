<?php

namespace App\Http\Controllers;

use App\Support\StationAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NozzleDisplayOrderController extends Controller
{
    public function show(Request $request, int $stationId): JsonResponse
    {
        $this->authorizeStation($request, $stationId);

        return response()->json(DB::table('nozzle_display_orders')
            ->where('station_id', $stationId)
            ->orderBy('sort_order')
            ->pluck('nozzle_id')->map(fn ($id) => (int) $id)->values());
    }

    public function update(Request $request, int $stationId): JsonResponse
    {
        $this->authorizeStation($request, $stationId);
        $validated = $request->validate([
            'nozzle_ids' => ['required', 'array', 'min:1'],
            'nozzle_ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $submittedIds = array_values(array_map('intval', $validated['nozzle_ids']));
        $stationIsLocal = (string) DB::table('stations')->where('id', $stationId)->value('local') === '1';
        $stationNozzleIds = DB::table('nozzles as n')
            ->join('dispensers as d', 'n.dispenser_id', '=', $stationIsLocal ? 'd.stationrow_id' : 'd.id')
            ->where('d.station_id', $stationId)->where('n.status', 1)->distinct()
            ->pluck('n.id')->map(fn ($id) => (int) $id)->values()->all();

        $expected = $stationNozzleIds;
        $received = $submittedIds;
        sort($expected);
        sort($received);
        if ($expected !== $received) {
            return response()->json(['message' => 'Nozzle list changed while arranging. Reload the page and arrange again.'], 409);
        }

        DB::transaction(function () use ($stationId, $submittedIds) {
            DB::table('nozzle_display_orders')->where('station_id', $stationId)->delete();
            $now = now();
            DB::table('nozzle_display_orders')->insert(array_map(fn ($nozzleId, $index) => [
                'station_id' => $stationId,
                'nozzle_id' => $nozzleId,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ], $submittedIds, array_keys($submittedIds)));
        });

        return response()->json(['message' => 'Nozzle arrangement saved successfully.']);
    }

    private function authorizeStation(Request $request, int $stationId): void
    {
        $user = $request->user();
        abort_unless($user && in_array($stationId, array_map('intval', StationAccess::ids($user)), true), 403);
    }
}
