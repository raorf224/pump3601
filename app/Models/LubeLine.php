<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LubeLine extends Model
{
    use HasFactory;

    protected $table = 'lube_lines';

    protected $fillable = [
        'document_id',
        'product_id',
        'qty',
        'unit_price',
        'line_amount',
        'tax_percent',
        'tax_amount',
    ];

    public function document()
    {
        return $this->belongsTo(LubeDocument::class, 'document_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
