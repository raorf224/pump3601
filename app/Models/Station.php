<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'lat',
        'lng',
        'location',
        'city',
        'status',
    ];

    /**
     * Get the user that owns the station.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function tanks()
    {
        return $this->hasMany(Tank::class);
    }

    public function dispensers()
    {
        return $this->hasMany(Dispenser::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
