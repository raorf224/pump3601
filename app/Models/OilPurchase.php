<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OilPurchase extends Model
{
    use HasFactory;

    protected $table = 'oil_purchase';

    protected $fillable = [
        'supplier_id',
        'tank_id',
        'station_id',
        'order_date',
        'recieving_date',
        'payment_status',
        'recieved_qty',
        'rate',
        'qty',
        'invoice_no',
        'ref_num',
        'stock_update',
        'created_by',
    ];

    // ✅ Supplier relationship (Account table)
    public function supplier()
    {
        return $this->belongsTo(Account::class, 'supplier_id');
    }

    // ✅ Correct Station relationship
    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    // ✅ Tank relationship
    public function tank()
    {
        return $this->belongsTo(Tank::class, 'tank_id');
    }
}
