<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sell extends Model
{
    use HasFactory;
    protected $fillable = [
        'cashier_id',
        'customer_id',
        'warehouse_id',
        'order_number',
        'subtotal',
        'grand_total',
        'pay',
        'change',
        'transaction_date',
        'payment_method',
        'status',
        'cash',
        'transfer',
    ];

    public function details()
    {
        return $this->hasMany(SellDetail::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sellReturs()
    {
        return $this->hasMany(SellRetur::class);
    }
}
