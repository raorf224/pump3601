<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollManagement extends Model
{
    protected $table = 'payrol_management';
    
    protected $fillable = [
        'station_id', 'employe_id', 'title', 'frequency', 'basic_pay',
        'net_pay', 'gross_pay', 'attendance_data', 'period_start',
        'period_end', 'pay_date', 'status'
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employe_id');
    }
    
    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }
}