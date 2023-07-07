<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendStockDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'send_stock_id',
        'product_id',
        'unit_id',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sendStock()
    {
        return $this->belongsTo(SendStock::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
