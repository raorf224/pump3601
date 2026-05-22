<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftNozzleReading extends Model
{
    use HasFactory;

    protected $table = 'shift_nozzle_readings';

    protected $fillable = [
        'shift_id',
        'nozzle_id',
        'opening_reading',
        'closing_reading', 
        'total_dispensed',
        'rate',
        'total_amount',
        'collected_from',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function nozzle()
    {
        return $this->belongsTo(Nozzle::class);
    }

    public function collectedFrom()
    {
        return $this->belongsTo(Employee::class, 'collected_from');
    }
}