<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'station_id',
        'role',
        'address',
        'city',
        'region',
        'country',
        'cnic',
        'phone',
        'salary',
        'status',
    ];

    /**
     * Get the user record associated with the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the station where the employee works.
     */
    public function station()
    {
        return $this->belongsTo(Station::class);
    }
        public function salaryComponents()
    {
        return $this->hasMany(EmployeeSalaryManagement::class, 'emloye_id');
    }
    
}
