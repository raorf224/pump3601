<?php
// app/Models/NozzleTotalizerReset.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NozzleTotalizerReset extends Model
{
    use HasFactory;

    protected $table = 'nozzle_totalizer_resets';

    protected $fillable = [
        'shift_id',
        'nozzle_id',
        'reset_date',
        'old_reading',
        'new_reading',
        'total_dispensed',
        'rate',
        'total_amount',
        'reason',
        'created_by',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function nozzle()
    {
        return $this->belongsTo(Nozzle::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}