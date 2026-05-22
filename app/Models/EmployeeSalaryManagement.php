<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryManagement extends Model
{
    protected $table = 'employe_salary_management';
    
    protected $fillable = ['emloye_id', 'component_id', 'status'];
    
    /**
     * Get the salary component associated with the employee salary management.
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }
    
    /**
     * Get the employee associated with the salary management.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emloye_id');
    }
}