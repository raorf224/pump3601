<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LubeDocument extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lube_documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'station_id',
        'doc_type',
        'account_id',
        'invoice_no',
        'date',
        'payment_status',
        'payment_method',
        'remarks',
        'created_by',
    ];

    /**
     * Get the station associated with the document.
     */
    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get the account (supplier/customer) associated with the document.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
public function lines()
{
    return $this->hasMany(LubeLine::class, 'document_id');
}
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

}
