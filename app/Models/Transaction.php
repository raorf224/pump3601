<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'station_id',
        'account_id',
        'shift_id',
        'type',
        'debit',
        'credit',
        'method',
        'to_account',
        'note',
    ];

    /**
     * The name of the "updated_at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;
     // Relations
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
    
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
    
    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account');
    }
    
    // Helper method to get amount
    public function getAmountAttribute()
    {
        return $this->type === 'income' ? $this->credit : $this->debit;
    }
    
    // Helper method to get cash/bank amount
    public function getCashAmountAttribute()
    {
        return $this->method === 'cash' ? $this->amount : 0;
    }
    
    public function getBankAmountAttribute()
    {
        return in_array($this->method, ['bank', 'card']) ? $this->amount : 0;
    }
}
