<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $fillable = [
        'supplier_id',
        'treasury_id',
        'warehouse_id',
        'treasury_id',
        'quantity',
        'invoice',
        'order_number',
        'subtotal',
        'grand_total',
        'pay',
        'due_date',
        'reciept_date',
        'description',
        'tax',
        'status'
    ];

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}
