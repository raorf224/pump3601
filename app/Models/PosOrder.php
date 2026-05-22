<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'order_id',
        'category_id',
        'product_id',
        'price',
        'quantity',
        'discount',
        'tax',
        'payment_type',
        'status',
        'remarks',
        'date',
        'total'
    ];
}
