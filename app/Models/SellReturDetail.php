<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellReturDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'sell_retur_id',
        'product_id',
        'unit_id',
        'qty',
    ];

    public function sellRetur()
    {
        return $this->belongsTo(SellRetur::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
