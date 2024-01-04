<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_retur_id',
        'product_id',
        'unit_id',
        'price',
        'qty',
    ];

    public function purchaseRetur()
    {
        return $this->belongsTo(PurchaseRetur::class);
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
