<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellCartDraft extends Model
{
    use HasFactory;
    protected $fillable = [
        'sell_id',
        'cashier_id',
        'unit_id',
        'product_id',
        'quantity',
        'price',
        'diskon',
    ];
    
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

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
