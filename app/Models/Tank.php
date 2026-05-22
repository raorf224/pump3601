<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tank extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tanks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'station_id',
        'product_id',
        'name',
        'capacity',
        'current_level',
        'status',
    ];

    /**
     * Get the product stored in the tank.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The dispensers that belong to the tank.
     */
    public function dispensers()
    {
        return $this->belongsToMany(Dispenser::class, 'tank_dispenser');
    }
        public function dips()
    {
        return $this->hasMany(TankDip::class);
    }

    /**
     * Get the nozzles connected to the tank.
     */
    public function nozzles()
    {
        return $this->hasMany(Nozzle::class);
    }
}
