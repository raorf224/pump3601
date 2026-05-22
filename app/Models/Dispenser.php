<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispenser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dispensers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'station_id',
        'name',
        'status',
    ];

    /**
     * The tanks that belong to the dispenser.
     */
    public function tanks()
    {
        return $this->belongsToMany(Tank::class, 'tank_dispenser');
    }
}
