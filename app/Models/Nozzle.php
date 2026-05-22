<?php
// app/Models/Nozzle.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nozzle extends Model
{
    use HasFactory;

    protected $table = 'nozzles';

    protected $fillable = [
        'dispenser_id',
        'name',
        'product_id',
        'tank_id',
        'intial_meter_reading',
        'status',
        'intial_date',
    ];

    public function dispenser()
    {
        return $this->belongsTo(Dispenser::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tank()
    {
        return $this->belongsTo(Tank::class);
    }

    public function shiftReadings()
    {
        return $this->hasMany(ShiftNozzleReading::class);
    }

    public function totalizerResets()
    {
        return $this->hasMany(NozzleTotalizerReset::class);
    }
}