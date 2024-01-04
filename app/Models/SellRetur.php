<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellRetur extends Model
{
    use HasFactory;
    protected $fillable = [
        'sell_id',
        'warehouse_id',
        'user_id',
        'retur_date'
    ];

    public function sell()
    {
        return $this->belongsTo(Sell::class);
    }

    public function detail()
    {
        return $this->hasMany(SellReturDetail::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
