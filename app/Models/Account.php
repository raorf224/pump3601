<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'station_id',
        'name',
        'phone',
        'email',
        'coords',
        'cnic',
        'address',
    ];

    /**
     * Get the station that owns the account.
     */
    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
