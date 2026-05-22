<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftCashFlow extends Model
{
    use HasFactory;
    
    protected $table = 'shift_cash_flow';
    
    protected $fillable = [
        'shift_id', 'shift_incharge', 'total_cash', 'in_hand',
        'in_bank', 'from_date', 'to_date'
    ];
    
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
        public function shiftIncharge()
    {
        return $this->belongsTo(Employee::class, 'shift_incharge');
    }
	public function getByShiftId($shiftId)
{
    $cashFlow = DB::select("SELECT * FROM shift_cash_flow WHERE shift_id = ?", [$shiftId]);
    return response()->json($cashFlow);
}
}