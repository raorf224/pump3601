<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EditCloseShiftController extends Controller
{
    /**
     * Display the edit close shift page
     */
    public function index($shift_id)
    {
        // Verify shift exists
        $shift = DB::select("SELECT * FROM shifts WHERE id = ?", [$shift_id]);

        if (empty($shift)) {
            abort(404, 'Shift not found');
        }

        return view('editshift', ['shift_id' => $shift_id]);
    }

    /**
     * Get all shift data for editing
     */
    public function getShiftData($shift_id)
    {
        $shift = DB::select("
            SELECT 
                s.*,
                st.name as station_name,
                u.full_name as shift_incharger_name,
                CASE WHEN s.shift_no = 1 THEN 'Day' ELSE 'Night' END as shift_type
            FROM shifts s
            LEFT JOIN stations st ON s.station_id = st.id
            LEFT JOIN employees e ON s.shift_incharger = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE s.id = ?
        ", [$shift_id]);

        if (empty($shift)) {
            return response()->json(['error' => 'Shift not found'], 404);
        }

        // Format dates for datetime-local input
        if ($shift[0]->start_time) {
            $shift[0]->start_time = Carbon::parse($shift[0]->start_time)->format('Y-m-d\TH:i');
        }
        if ($shift[0]->end_time) {
            $shift[0]->end_time = Carbon::parse($shift[0]->end_time)->format('Y-m-d\TH:i');
        }

        return response()->json($shift[0]);
    }

    /**
     * Get cash flow data for shift
     */
    public function getCashFlow($shift_id)
    {
        $cashFlow = DB::select("
            SELECT * FROM shift_cash_flow 
            WHERE shift_id = ?
            LIMIT 1
        ", [$shift_id]);

        return response()->json(!empty($cashFlow) ? $cashFlow[0] : null);
    }

    /**
     * Get driver credits for shift
     */
    public function getDriverCredits($shift_id)
    {
        $credits = DB::select("
            SELECT 
                dc.*,
                s.name as station_name,
                a.name as customer_name
            FROM credit_driver dc
            LEFT JOIN stations s ON dc.station_id = s.id
            LEFT JOIN accounts a ON dc.account_id = a.id
            WHERE dc.shift_id = ?
            ORDER BY dc.id ASC
        ", [$shift_id]);

        return response()->json($credits);
    }

    /**
     * Get tank dips for shift
     */
    public function getTankDips($shift_id)
    {
        $tankDips = DB::select("
            SELECT 
                td.*,
                t.name as tank_name,
                t.capacity,
                t.current_level,
                t.current_level_mm,
                p.name as product_name
            FROM tanks_dip td
            INNER JOIN tanks t ON td.tank_id = t.id
            LEFT JOIN products p ON t.product_id = p.id
            WHERE td.shift_id = ?
        ", [$shift_id]);

        return response()->json($tankDips);
    }

    /**
     * Get nozzle readings for shift
     */
    public function getNozzleReadings($shift_id)
    {
        $readings = DB::select("
            SELECT 
                snr.*,
                n.name as nozzle_name,
                n.intial_meter_reading,
                d.name as dispenser_name,
                p.name as product_name,
                t.name as tank_name
            FROM shift_nozzle_readings snr
            INNER JOIN nozzles n ON snr.nozzle_id = n.id
            LEFT JOIN dispensers d ON n.dispenser_id = d.id
            LEFT JOIN products p ON n.product_id = p.id
            LEFT JOIN tanks t ON n.tank_id = t.id
            WHERE snr.shift_id = ?
        ", [$shift_id]);

        return response()->json($readings);
    }

    /**
     * Update closed shift with all changes
     */
    public function updateClosedShift(Request $request, $shift_id)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();
            $userId = auth()->id();

            // 1. Update shift table
            if (isset($data['shift_data'])) {
                $shiftData = $data['shift_data'];

                DB::update("
                    UPDATE shifts 
                    SET end_time = ?, 
                        status = ?, 
                        cash_return = ?,
                        updated_at = NOW(),
                        is_edited = 1
                    WHERE id = ?
                ", [
                    $shiftData['end_time'],
                    $shiftData['status'],
                    $shiftData['cash_return'],
                    $shift_id
                ]);
            }

            // 2. Update cash flow
            // 2. Update or insert cash flow
            if (isset($data['cash_flow'])) {
                $cf = $data['cash_flow'];

                // Check if exists
                $exists = DB::select("SELECT id FROM shift_cash_flow WHERE shift_id = ?", [$shift_id]);

                if (!empty($exists)) {
                    DB::update("
            UPDATE shift_cash_flow 
            SET total_cash = ?, 
                in_hand = ?, 
                in_bank = ?, 
                fuelcard = ?, 
                creditcard = ?,
                faccountid = ?,
                caccountid = ?,
                updated_at = NOW()
            WHERE shift_id = ?
        ", [
                        $cf['total_cash'],
                        $cf['in_hand'],
                        $cf['in_bank'],
                        $cf['fuel_card'] ?? 0,
                        $cf['credit_card'] ?? 0,
                        $cf['faccountid'] ?? null,
                        $cf['caccountid'] ?? null,
                        $shift_id
                    ]);
                } else {
                    DB::insert("
            INSERT INTO shift_cash_flow 
            (shift_id, total_cash, in_hand, in_bank, fuelcard, creditcard, 
             faccountid, caccountid, from_date, to_date, station_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [
                        $shift_id,
                        $cf['total_cash'],
                        $cf['in_hand'],
                        $cf['in_bank'],
                        $cf['fuel_card'] ?? 0,
                        $cf['credit_card'] ?? 0,
                        $cf['faccountid'] ?? null,
                        $cf['caccountid'] ?? null,
                        $cf['from_date'],
                        $cf['to_date'],
                        $data['station_id'] ?? null
                    ]);
                }
            }

            // 3. Update tank dips
            // 3. Update tank dips
if (isset($data['tank_updates']) && !empty($data['tank_updates'])) {
    foreach ($data['tank_updates'] as $tank) {
        // Pehle current dip reading le lo (original value)
        $currentDip = DB::select("
            SELECT dip_in_liters FROM tanks_dip 
            WHERE shift_id = ? AND tank_id = ?
        ", [$shift_id, $tank['tank_id']]);
        
        $oldDipLiters = !empty($currentDip) ? floatval($currentDip[0]->dip_in_liters) : 0;
        $newDipLiters = floatval($tank['dip_in_liters']);
        
        // Difference calculate karo (old - new)
        $difference = $oldDipLiters - $newDipLiters;
        
        // Update tanks_dip table
        DB::update("
            UPDATE tanks_dip 
            SET dip_mm = ?, 
                dip_in_liters = ?,
                updated_at = NOW()
            WHERE shift_id = ? AND tank_id = ?
        ", [
            $tank['dip_mm'],
            $newDipLiters,
            $shift_id,
            $tank['tank_id']
        ]);
        
        // 🔥 Agar difference positive hai (consumption increase hua hai)
        if ($difference != 0) {
            DB::update("
                UPDATE tanks 
                SET current_level = current_level - ?,
                    updated_at = NOW()
                WHERE id = ?
            ", [$difference, $tank['tank_id']]);
        }
    }
}

            // 4. Update nozzle readings
            // 4. Update nozzle readings
if (isset($data['nozzle_updates']) && !empty($data['nozzle_updates'])) {
    foreach ($data['nozzle_updates'] as $nozzle) {
        
        // Get current closing reading from database
        $currentReading = DB::select("
            SELECT closing_reading FROM shift_nozzle_readings 
            WHERE shift_id = ? AND nozzle_id = ?
        ", [$shift_id, $nozzle['nozzle_id']]);
        
        $oldClosingReading = !empty($currentReading) ? floatval($currentReading[0]->closing_reading) : 0;
        $newClosingReading = floatval($nozzle['closing_reading']);
        $difference = $newClosingReading - $oldClosingReading;
        
        // 🔥 DEBUG LOGS
        \Log::info("========== NOZZLE UPDATE DEBUG ==========");
        \Log::info("Nozzle ID: " . $nozzle['nozzle_id']);
        \Log::info("Shift ID: " . $shift_id);
        \Log::info("Old Closing Reading: " . $oldClosingReading);
        \Log::info("New Closing Reading: " . $newClosingReading);
        \Log::info("Difference: " . $difference);
        
        // Update shift_nozzle_readings
        DB::update("
            UPDATE shift_nozzle_readings 
            SET closing_reading = ?, 
                testing_reading = ?,
                updated_at = NOW()
            WHERE shift_id = ? AND nozzle_id = ?
        ", [
            $newClosingReading,
            $nozzle['testing'] ?? 0,
            $shift_id,
            $nozzle['nozzle_id']
        ]);
        
        // Update nozzles intial_meter_reading with updated_at
        if ($difference != 0) {
            $affected = DB::update("
                UPDATE nozzles 
                SET intial_meter_reading = intial_meter_reading + ?,
                    updated_at = NOW()
                WHERE id = ?
            ", [$difference, $nozzle['nozzle_id']]);
            
            \Log::info("Nozzles Update - Rows Affected: " . $affected);
            
            // Verify new value
            $newValue = DB::select("SELECT intial_meter_reading, updated_at FROM nozzles WHERE id = ?", [$nozzle['nozzle_id']]);
            \Log::info("New intial_meter_reading: " . ($newValue[0]->intial_meter_reading ?? 'NULL'));
            \Log::info("Nozzle updated_at: " . ($newValue[0]->updated_at ?? 'NULL'));
        } else {
            \Log::info("Difference is ZERO - No update to nozzles table");
        }
    }
}

            // 5. Handle driver credits - Updates
            if (isset($data['driver_credit_updates']) && !empty($data['driver_credit_updates'])) {
                foreach ($data['driver_credit_updates'] as $credit) {
                    if (isset($credit['id'])) {
                        // Update existing
                        DB::update("
                            UPDATE credit_driver 
                            SET station_id = ?, 
                                account_id = ?, 
                                amount_given_to = ?, 
                                amount = ?, 
                                cnic = ?, 
                                vehicle_number = ?,
                                updated_at = NOW()
                            WHERE id = ? AND shift_id = ?
                        ", [
                            $credit['station_id'],
                            $credit['account_id'],
                            $credit['amount_given_to'],
                            $credit['amount'],
                            $credit['cnic'] ?? null,
                            $credit['vehicle_number'] ?? null,
                            $credit['id'],
                            $shift_id
                        ]);
                    } else {
                        // Insert new
                        DB::insert("
                            INSERT INTO credit_driver 
                            (shift_id, station_id, account_id, amount_given_to, amount, cnic, vehicle_number, created_by, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ", [
                            $shift_id,
                            $credit['station_id'],
                            $credit['account_id'],
                            $credit['amount_given_to'],
                            $credit['amount'],
                            $credit['cnic'] ?? null,
                            $credit['vehicle_number'] ?? null,
                            $credit['created_by'] ?? null,
                        ]);
                    }
                }
            }

            // 6. Delete removed driver credits
            if (isset($data['driver_credit_deletes']) && !empty($data['driver_credit_deletes'])) {
                $placeholders = implode(',', array_fill(0, count($data['driver_credit_deletes']), '?'));
                $params = array_merge($data['driver_credit_deletes'], [$shift_id]);
                DB::delete("DELETE FROM credit_driver WHERE id IN ({$placeholders}) AND shift_id = ?", $params);
            }

            // 7. INSERT new expenses (without deleting old ones)
            if (isset($data['expenses']) && !empty($data['expenses'])) {
                foreach ($data['expenses'] as $expense) {
                    $inserted = DB::insert("
            INSERT INTO transactions 
            (station_id, shift_id, type, debit, credit, method, note, created_at, updated_at)
            VALUES (?, ?, 'expense', ?, 0, 'cash', ?,  NOW(), NOW())
        ", [
                        $expense['station_id'],
                        $expense['shift_id'],
                        $expense['amount'],
                        $expense['note'] ?? null
                    ]);

                    // Debug log
                    \Log::info("Expense inserted: ", ['expense' => $expense, 'inserted' => $inserted]);
                }
            }

            // ==============================================
// UPDATE NEXT SHIFT (if exists) WITH NEW READINGS
// ==============================================

            // Get current shift to find next shift
            $currentShift = DB::select("SELECT station_id, start_time FROM shifts WHERE id = ?", [$shift_id]);

            if (!empty($currentShift)) {
                $currentStationId = $currentShift[0]->station_id;
                $currentStartTime = $currentShift[0]->start_time;

                // Find next shift for SAME STATION only (by start time, not by ID)
                $nextShift = DB::select("
                        SELECT id FROM shifts 
                        WHERE station_id = ? 
                        AND start_time > ?
                        ORDER BY start_time ASC 
                        LIMIT 1
                    ", [$currentStationId, $currentStartTime]);

                if (!empty($nextShift)) {
                    $nextShiftId = $nextShift[0]->id;

                    // 1. Update cash handover in next shift
                    if (isset($data['shift_data']['cash_return'])) {
                        $newCashReturn = floatval($data['shift_data']['cash_return']);
                        DB::update("
                            UPDATE shifts 
                            SET cash_handover = ?, updated_at = NOW()
                            WHERE id = ?
                        ", [$newCashReturn, $nextShiftId]);
                    }

                    // 2. Update nozzle readings for next shift
                    $updatedNozzles = DB::select("
                                SELECT nozzle_id, closing_reading 
                                FROM shift_nozzle_readings 
                                WHERE shift_id = ?
                            ", [$shift_id]);

                    foreach ($updatedNozzles as $nozzle) {
                        // Update shift_nozzle_readings table (opening_reading for next shift)
                        DB::update("
                            UPDATE shift_nozzle_readings 
                            SET opening_reading = ?, updated_at = NOW()
                            WHERE shift_id = ? AND nozzle_id = ?
                        ", [$nozzle->closing_reading, $nextShiftId, $nozzle->nozzle_id]);

                        // Update nozzles table (intial_meter_reading)
                        // DB::update("
                        //     UPDATE nozzles 
                        //     SET intial_meter_reading = ?, updated_at = NOW()
                        //     WHERE id = ?
                        // ", [$nozzle->closing_reading, $nozzle->nozzle_id]);
                    }

                    // 3. UPDATE TANK DIPS FOR NEXT SHIFT (🔥 NEW CODE)
                    $updatedTanks = DB::select("
                        SELECT tank_id, dip_in_liters, dip_mm, from_date, to_date
                        FROM tanks_dip 
                        WHERE shift_id = ?
                    ", [$shift_id]);

                    foreach ($updatedTanks as $tank) {
                        // Check if next shift already has a tanks_dip entry
                        $existingDip = DB::select("
                            SELECT id FROM tanks_dip 
                            WHERE shift_id = ? AND tank_id = ?
                            LIMIT 1
                        ", [$nextShiftId, $tank->tank_id]);

                        if (!empty($existingDip)) {
                            // UPDATE - Sirf old fields update karo, dip_mm aur dip_in_liters ko mat chhedo
                            DB::update("
                                UPDATE tanks_dip 
                                SET old_dip_mm = ?, 
                                    old_dip_liters = ?,
                                    updated_at = NOW()
                                WHERE shift_id = ? AND tank_id = ?
                            ", [
                                $tank->dip_mm,           // current shift ka dip_mm → old_dip_mm
                                $tank->dip_in_liters,    // current shift ka dip_in_liters → old_dip_liters
                                $nextShiftId,
                                $tank->tank_id
                            ]);
                        } else {
                            // INSERT - Sirf old fields ke saath insert karo, dip_mm aur dip_in_liters NULL ya 0 rakho
                            DB::insert("
                                INSERT INTO tanks_dip 
                                (tank_id, shift_id, old_dip_mm, old_dip_liters, dip_mm, dip_in_liters,
                                from_date, to_date, created_by, created_at, updated_at, station_id)
                                VALUES (?, ?, ?, ?, 0, 0, ?, ?, ?, NOW(), NOW(), ?)
                            ", [
                                $tank->tank_id,
                                $nextShiftId,
                                $tank->dip_mm,           // current shift ka dip_mm → old_dip_mm
                                $tank->dip_in_liters,    // current shift ka dip_in_liters → old_dip_liters
                                $tank->from_date,
                                $tank->to_date,
                                $userId,
                                $currentStationId
                            ]);

                        }
                    }

                    // 4. Update tanks table (current_level)
                    //         foreach ($updatedTanks as $tank) {
                    //             DB::update("
                    //     UPDATE tanks 
                    //     SET current_level = ?, 
                    //         current_level_mm = ?, 
                    //         updated_at = NOW()
                    //     WHERE id = ?
                    // ", [$tank->dip_in_liters, $tank->dip_mm, $tank->tank_id]);
                    //         }
                }
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shift updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating shift: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Get bank transfer details for shift
     */
    /**
     * Get fuel card details for shift
     */
    public function getFuelCardDetails($shiftId)
    {
        // Get fuel card from shift_cash_flow table
        $fuelCard = DB::select("
        SELECT sf.*, a.name as account_name, a.account_number, a.bank_name, a.type
        FROM shift_cash_flow sf 
        LEFT JOIN accounts a ON sf.faccountid = a.id 
        WHERE sf.shift_id = ?
    ", [$shiftId]);

        return response()->json(!empty($fuelCard) ? $fuelCard[0] : null);
    }

    /**
     * Get credit card details for shift
     */
    public function getCreditCardDetails($shiftId)
    {
        // Get credit card from shift_cash_flow table
        $creditCard = DB::select("
        SELECT sf.*, a.name as account_name, a.account_number, a.bank_name, a.type
        FROM shift_cash_flow sf 
        LEFT JOIN accounts a ON sf.caccountid = a.id 
        WHERE sf.shift_id = ?
    ", [$shiftId]);

        return response()->json(!empty($creditCard) ? $creditCard[0] : null);
    }

    /**
     * Get bank transfer details for shift
     */
    public function getBankTransferDetails($shiftId)
    {
        // Get bank transfer from shift_cash_flow table
        $bankTransfer = DB::select("
        SELECT sf.*, a.name as account_name, a.account_number, a.bank_name
        FROM shift_cash_flow sf 
        LEFT JOIN accounts a ON sf.baccountid = a.id 
        WHERE sf.shift_id = ?
    ", [$shiftId]);

        return response()->json(!empty($bankTransfer) ? $bankTransfer[0] : null);
    }
}