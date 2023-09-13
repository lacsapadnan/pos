<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    use HasFactory;
    protected $fillable = [
        'warehouse_id',
        'for',
        'description',
        'in',
        'out',
        'payment_method',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
