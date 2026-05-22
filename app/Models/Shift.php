<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'station_id',
        'shift_no', 
        'shift_incharger',
        'start_time',
        'end_time',
        'status',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function shiftIncharger()
    {
        return $this->belongsTo(Employee::class, 'shift_incharger');
    }

    public function nozzleReadings()
    {
        return $this->hasMany(ShiftNozzleReading::class);
    }

    public function nozzleResets()
    {
        return $this->hasMany(NozzleTotalizerReset::class);
    }
}