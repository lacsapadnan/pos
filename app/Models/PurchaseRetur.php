<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRetur extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_id',
        'warehouse_id',
        'user_id',
        'retur_date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseReturDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
