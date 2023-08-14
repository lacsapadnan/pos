<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellReturCart extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'unit_id', 'price', 'quantity', 'user_id', 'sell_id'];

    public function sell()
    {
        return $this->belongsTo(Sell::class);
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
