<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    protected $table = 'salary_componenet';
    
    protected $fillable = [
        'component_name',
        'type',
        'calculation',
        'cal_ammount',
        'mandatory',
        'status'
    ];
    
    protected $casts = [
        'cal_ammount' => 'integer',
    ];
    
    /**
     * Get the employee salary managements for the salary component.
     */
    public function employeeSalaryManagements(): HasMany
    {
        return $this->hasMany(EmployeeSalaryManagement::class, 'component_id');
    }
    
    /**
     * Scope active components.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
    
    /**
     * Scope earning components.
     */
    public function scopeEarnings($query)
    {
        return $query->where('type', 'Earning');
    }
    
    /**
     * Scope deduction components.
     */
    public function scopeDeductions($query)
    {
        return $query->where('type', 'Deduction');
    }
}