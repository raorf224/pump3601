<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TankDip extends Model
{
    use HasFactory;

    protected $table = 'tanks_dip';

    protected $fillable = [
        'tank_id',
        'dip_mm',
        'dip_in_liters',
        'old_dip_mm',
        'old_dip_liters',
        'remarks',
        'from_date',
        'to_date',
        'created_by',
    ];

    public function tank()
    {
        return $this->belongsTo(Tank::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}