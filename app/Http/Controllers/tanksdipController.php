<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;
use Carbon\Carbon; // Add this line



class tanksdipController extends Controller
{

    /**
     * Store tank dip readings - UPDATED WITH FROM_DATE & TO_DATE
     */
 public function storeTankDipReadings(Request $request)
{
    

    try {
        $request->validate([
            'tank_data' => 'required|array',
            'tank_data.*.tank_id' => 'required|integer|exists:tanks,id',
            'tank_data.*.dip_mm' => 'nullable|numeric|min:0',
            'tank_data.*.dip_in_liters' => 'nullable|numeric|min:0',
            'tank_data.*.from_date' => 'required|date',
            'tank_data.*.to_date' => 'required|date',
            'tank_data.*.shift_id' => 'nullable|integer|exists:shifts,id',
            'tank_data.*.remarks' => 'nullable|string|max:255',
            'tank_data.*.created_by' => 'required|integer'
        ]);

        $savedReadings = [];

        foreach ($request->tank_data as $tankReading) {
            // Check if at least one reading is provided
            if (empty($tankReading['dip_mm']) && empty($tankReading['dip_in_liters'])) {
                continue;
            }

            // ✅ GET TANK DETAILS (capacity aur current level)
            $tank = DB::table('tanks')
                ->where('id', $tankReading['tank_id'])
                ->select('capacity', 'current_level', 'current_level_mm')
                ->first();

            if (!$tank) {
                continue; // Skip if tank not found
            }

            $tankCapacity = (float) $tank->capacity;
            $currentTankLevel = (float) $tank->current_level; // Tank ka abhi ka level

            // ✅ VALIDATE DIP IN LITERS AGAINST TANK CAPACITY
            if (isset($tankReading['dip_in_liters']) && $tankReading['dip_in_liters'] > 0) {
                $dipLiters = (float) $tankReading['dip_in_liters'];

                if ($dipLiters > $tankCapacity) {
                    return response()->json([
                        "success" => false,
                        "message" => "Dip in liters ({$dipLiters}) cannot exceed tank capacity ({$tankCapacity}) for tank ID: {$tankReading['tank_id']}",
                        "error" => "dip_liters_exceeds_capacity"
                    ], 422);
                }

                // ✅ IMPORTANT: COMPARE WITH PREVIOUS TANK LEVEL
                if ($dipLiters > $currentTankLevel) {
                    return response()->json([
                        "success" => false,
                        "message" => "New dip reading ({$dipLiters}L) is GREATER than current tank level ({$currentTankLevel}L). Check your reading!",
                        "tank_id" => $tankReading['tank_id'],
                        "current_tank_level" => $currentTankLevel,
                        "new_dip_reading" => $dipLiters
                    ], 422);
                }
            }

            // ✅ GET PREVIOUS DIP READING FROM tanks_dip TABLE
            $previousReading = DB::table('tanks_dip')
                ->where('tank_id', $tankReading['tank_id'])
                ->orderBy('to_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            // ✅ SET OLD VALUES
            if (!$previousReading) {
                // First reading in tanks_dip table
                $oldDipLiters = $tank->current_level ?? 0;
                $oldDipMm = $tank->current_level_mm ?? 0;
            } else {
                $oldDipLiters = $previousReading->dip_in_liters ?? 0;
                $oldDipMm = $previousReading->dip_mm ?? 0;
            }

            $fromDate = $tankReading['from_date'];
            $toDate = $tankReading['to_date'];

            $currentDipMm = isset($tankReading['dip_mm']) ? (float) $tankReading['dip_mm'] : 0;
            $currentDipLiters = isset($tankReading['dip_in_liters']) ? (float) $tankReading['dip_in_liters'] : 0;

            $reading = [
                'tank_id' => $tankReading['tank_id'],
                'shift_id' => $tankReading['shift_id'] ?? null,
                
                // ✅ CURRENT/NEW DIP READINGS
                'dip_mm' => $currentDipMm,
                'dip_in_liters' => $currentDipLiters,
                
                // ✅ OLD/PREVIOUS READINGS
                'old_dip_mm' => $oldDipMm,
                'old_dip_liters' => $oldDipLiters,
                
                // ✅ DATE RANGE
                'from_date' => $fromDate,
                'to_date' => $toDate,
                
                'remarks' => $tankReading['remarks'] ?? null,
                'created_by' => $tankReading['created_by'],
                'created_at' => now()
            ];

            // Insert into tanks_dip table
            $readingId = DB::table('tanks_dip')->insertGetId($reading);
            
            // ✅ IMPORTANT: UPDATE TANK'S CURRENT LEVEL WITH NEW DIP READING
            // Jab dip liya jata hai, toh wahi tank ka current level ban jata hai
			
			
            DB::table('tanks')
                ->where('id', $tankReading['tank_id'])
                ->update([
                    'current_level' => $currentDipLiters > 0 ? $currentDipLiters : $tank->current_level,
                    'current_level_mm' => $currentDipMm > 0 ? $currentDipMm : $tank->current_level_mm,
                    'updated_at' => now()
                ]);

		
            // Add to response
            $savedReading = $reading;
            $savedReading['id'] = $readingId;
            $savedReading['tank_capacity'] = $tankCapacity;
            $savedReading['previous_tank_level'] = $currentTankLevel;
            $savedReadings[] = $savedReading;
        }

        return response()->json([
            "success" => true,
            "message" => "Tank dip readings saved successfully",
            "data" => $savedReadings
        ], 201);

    } catch (\Exception $e) {
        \Log::error('Error saving tank dip readings:', ['error' => $e->getMessage()]);
        return response()->json([
            "success" => false,
            "message" => "Failed to save tank dip readings",
            "error" => $e->getMessage()
        ], 500);
    }
}


    /**
     * Get tank dip readings by station
     */
    public function getTankDipReadingsByStation($stationId)
    {
        try {
            $stationrec = DB::select('select * from stations where id=?',[$stationId]);
            if($stationrec[0]->local =="1"){
                $readings = DB::table('tanks_dip')
                ->join('tanks', 'tanks_dip.tank_id', '=', 'tanks.stationrow_id')

                // Join for created_by user
                ->leftJoin('users as created_user', 'tanks_dip.created_by', '=', 'created_user.stationrow_id')


                ->where('tanks.station_id', $stationId)

                ->select(
                    'tanks_dip.*',
                    'tanks.name as tank_name',
                    'tanks.station_id',

                    // Full names
                    DB::raw('created_user.full_name as created_by_name'),
                )

                ->orderBy('tanks_dip.created_at', 'desc')
                ->get();

            }else{

            
            $readings = DB::table('tanks_dip')
                ->join('tanks', 'tanks_dip.tank_id', '=', 'tanks.id')

                // Join for created_by user
                ->leftJoin('users as created_user', 'tanks_dip.created_by', '=', 'created_user.id')


                ->where('tanks.station_id', $stationId)

                ->select(
                    'tanks_dip.*',
                    'tanks.name as tank_name',
                    'tanks.station_id',

                    // Full names
                    DB::raw('created_user.full_name as created_by_name'),
                )

                ->orderBy('tanks_dip.created_at', 'desc')
                ->get();
}
            return response()->json($readings);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tank dip readings',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Delete tank dip reading
     */
    public function deleteTankDipReading($id)
    {
        try {
            $deleted = DB::table('tanks_dip')->where('id', $id)->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tank dip reading deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tank dip reading not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tank dip reading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tank gain loss report - UPDATED VERSION
     */
    public function getTankGainLossReport($dipReadingId)
    {
        try {
            // Get reading + tank info
            $currentReading = DB::table('tanks_dip')
                ->join('tanks', 'tanks_dip.tank_id', '=', 'tanks.id')
                ->join('products', 'tanks.product_id', '=', 'products.id')
                ->leftJoin('users', 'tanks_dip.created_by', '=', 'users.id')
                ->where('tanks_dip.id', $dipReadingId)
                ->select(
                    'tanks_dip.*',
                    'tanks.name as tank_name',
                    'tanks.capacity',
                    'tanks.station_id',
                    'products.name as product_name',
                    'products.category',
                    'users.full_name as recorded_by'
                )
                ->first();

            if (!$currentReading) {
                return response()->json(['success' => false, 'message' => 'Dip reading not found'], 404);
            }

            // ✅ CALCULATE SALES USING FROM_DATE AND TO_DATE
            $salesData = $this->calculateSalesBetweenReadings(
                $currentReading->tank_id,
                $currentReading->old_dip_liters,
                $currentReading->dip_in_liters,
                $currentReading // Pass the whole reading object
            );

            $gainLossData = $this->calculateGainLoss(
                $currentReading->old_dip_liters,
                $currentReading->dip_in_liters,
                $salesData['total_sales_liters'],
                $salesData['total_sales_amount']
            );

            // Prepare final array
            $reportData = [
                'current_reading' => (array) $currentReading,
                'sales_data' => $salesData,
                'gain_loss_data' => $gainLossData,
                'report_generated_at' => now()->format('Y-m-d H:i:s'),
                'report_period' => $this->getReportPeriod($currentReading->from_date, $currentReading->to_date)
            ];

            return response()->json([
                'success' => true,
                'data' => $reportData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report period description - UPDATED VERSION
     */
    private function getReportPeriod($fromDate, $toDate)
    {
        return "From " . date('Y-m-d H:i', strtotime($fromDate)) . " to " . date('Y-m-d H:i', strtotime($toDate));
    }

    /**
     * Calculate sales between two dip readings - UPDATED VERSION
     */
    private function calculateSalesBetweenReadings($tankId, $oldLiters, $currentLiters, $currentReading)
    {
        // Get nozzles connected to this tank
        $nozzles = DB::table('nozzles')
            ->where('tank_id', $tankId)
            ->where('status', 1)
            ->pluck('id');

        if ($nozzles->isEmpty()) {
            return [
                'total_sales_liters' => 0,
                'total_sales_amount' => 0,
                'nozzle_sales' => []
            ];
        }

        // ✅ USE FROM_DATE AND TO_DATE FROM CURRENT READING
        $startDate = $currentReading->from_date;
        $endDate = $currentReading->to_date;

        // ✅ CALCULATE SALES USING created_at BETWEEN from_date AND to_date
        $shiftSales = DB::table('shift_nozzle_readings')
            ->join('nozzles', 'shift_nozzle_readings.nozzle_id', '=', 'nozzles.id')
            ->whereIn('shift_nozzle_readings.nozzle_id', $nozzles)
            ->whereBetween('shift_nozzle_readings.created_at', [$startDate, $endDate])
            ->select(
                'shift_nozzle_readings.nozzle_id',
                'nozzles.name as nozzle_name',
                DB::raw('SUM(shift_nozzle_readings.total_dispensed) as total_liters'),
                DB::raw('SUM(shift_nozzle_readings.total_amount) as total_amount')
            )
            ->groupBy('shift_nozzle_readings.nozzle_id', 'nozzles.name')
            ->get();

        $totalSalesLiters = $shiftSales->sum('total_liters');
        $totalSalesAmount = $shiftSales->sum('total_amount');

        return [
            'total_sales_liters' => $totalSalesLiters,
            'total_sales_amount' => $totalSalesAmount,
            'nozzle_sales' => $shiftSales,
            'period_start' => $startDate,
            'period_end' => $endDate
        ];
    }

    /**
     * Calculate gain/loss
     */
    private function calculateGainLoss($oldLiters, $currentLiters, $salesLiters, $salesAmount)
    {
        if (!$oldLiters || !$currentLiters) {
            return [
                'physical_stock_change' => 0,
                'expected_stock' => 0,
                'variance_liters' => 0,
                'variance_percentage' => 0,
                'variance_amount' => 0,
                'status' => 'no_data'
            ];
        }

        $physicalStockChange = $oldLiters - $currentLiters;
        $expectedStock = $oldLiters - $salesLiters;
        $varianceLiters = $currentLiters - $expectedStock;

        // Calculate variance percentage
        $variancePercentage = $expectedStock != 0 ? ($varianceLiters / $expectedStock) * 100 : 0;

        // Calculate monetary value of variance
        $averagePrice = $salesLiters > 0 ? $salesAmount / $salesLiters : 0;
        $varianceAmount = $varianceLiters * $averagePrice;

        $status = 'normal';
        if (abs($variancePercentage) > 5) {
            $status = $varianceLiters > 0 ? 'gain' : 'loss';
        }

        return [
            'physical_stock_change' => $physicalStockChange,
            'expected_stock' => $expectedStock,
            'variance_liters' => $varianceLiters,
            'variance_percentage' => $variancePercentage,
            'variance_amount' => $varianceAmount,
            'average_price' => $averagePrice,
            'status' => $status
        ];
    }

    /**
     * Generate PDF Report - FIXED VERSION
     */
    public function downloadGainLossReportPDF($dipReadingId)
    {
        try {
            // Get reading + tank details
            $currentReading = DB::table('tanks_dip')
                ->join('tanks', 'tanks_dip.tank_id', '=', 'tanks.id')
                ->join('products', 'tanks.product_id', '=', 'products.id')
                ->leftJoin('users', 'tanks_dip.created_by', '=', 'users.id')
                ->where('tanks_dip.id', $dipReadingId)
                ->select(
                    'tanks_dip.*',
                    'tanks.name as tank_name',
                    'tanks.capacity',
                    'tanks.station_id',
                    'products.name as product_name',
                    'products.category',
                    'users.full_name as recorded_by'
                )
                ->first();

            if (!$currentReading) {
                return response()->json(['success' => false, 'message' => 'Dip reading not found'], 404);
            }

            // Calculate sales & gain/loss
            $salesData = $this->calculateSalesBetweenReadings(
                $currentReading->tank_id,
                $currentReading->old_dip_liters,
                $currentReading->dip_in_liters,
                $currentReading->Reading_date_Time
            );

            $gainLossData = $this->calculateGainLoss(
                $currentReading->old_dip_liters,
                $currentReading->dip_in_liters,
                $salesData['total_sales_liters'],
                $salesData['total_sales_amount']
            );

            // Prepare data for PDF
            $pdfData = [
                'reportData' => [
                    'current_reading' => (array) $currentReading,
                    'sales_data' => $salesData,
                    'gain_loss_data' => $gainLossData,
                    'report_generated_at' => now()->format('Y-m-d H:i:s'),
                    'report_period' => $this->getReportPeriod($currentReading->Reading_date_Time)
                ]
            ];
            // Load PDF view
            $pdf = PDF::loadView('gain-loss-pdf', $pdfData);

            return $pdf->download("GainLoss_Report_" . now()->format('Y_m_d_His') . ".pdf");

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getLastDip($tankId)
    {
        try {
            // ✅ 1. Tanks table se current level aur timestamps lein
            $tank = DB::table('tanks')
                ->where('id', $tankId)
                ->select(
                    'current_level',
                    'current_level_mm',
                    'created_at as tank_created_at',
                    'updated_at as tank_updated_at'
                )
                ->first();

            if (!$tank) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tank not found'
                ], 404);
            }

            // ✅ 2. Tanks_dip table se last dip aur timestamp lein
            $lastDip = DB::table('tanks_dip')
                ->where('tank_id', $tankId)
                ->orderBy('created_at', 'desc')
                ->select(
                    'dip_mm',
                    'dip_in_liters',
                    'created_at as dip_created_at'
                )
                ->first();

            // ✅ 3. Agar tanks_dip mein koi record nahi hai
            if (!$lastDip) {
                return response()->json([
                    'dip_mm' => $tank->current_level_mm,
                    'dip_in_liters' => $tank->current_level,
                    'source' => 'tanks_table_only',
                    'tank_updated_at' => $tank->tank_updated_at
                ]);
            }

            // ✅ 4. Compare karo timestamps - jo recent ho woh use karo
            $tankLatestTime = max(
                Carbon::parse($tank->tank_created_at),
                Carbon::parse($tank->tank_updated_at)
            );

            $dipTime = Carbon::parse($lastDip->dip_created_at);

            if ($tankLatestTime->greaterThan($dipTime)) {
                // Tank table zyada recent hai
                return response()->json([
                    'dip_mm' => $tank->current_level_mm,
                    'dip_in_liters' => $tank->current_level,
                    'source' => 'tanks_table',
                    'tank_latest_time' => $tankLatestTime,
                    'dip_time' => $dipTime,
                    'comparison' => 'tank_more_recent'
                ]);
            } else {
                // Tanks_dip zyada recent hai
                return response()->json([
                    'dip_mm' => $lastDip->dip_mm,
                    'dip_in_liters' => $lastDip->dip_in_liters,
                    'source' => 'tanks_dip_table',
                    'dip_time' => $dipTime,
                    'tank_latest_time' => $tankLatestTime,
                    'comparison' => 'dip_more_recent'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch last dip reading',
                'error' => $e->getMessage()
            ], 500);
        }
    }
	public function getByShiftAndTank($shiftId, $tankId)
{
    $dips = DB::select("SELECT * FROM tanks_dip WHERE shift_id = ? AND tank_id = ?", [$shiftId, $tankId]);
    return response()->json($dips);
}


}