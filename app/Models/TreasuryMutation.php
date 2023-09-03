<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryMutation extends Model
{
    use HasFactory;
    protected $fillable = [
        'from_warehouse',
        'to_warehouse',
        'input_cashier',
        'output_cashier',
        'from_treasury',
        'to_treasury',
        'amount',
        'description',
        'input_date',
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse');
    }

    public function inputCashier()
    {
        return $this->belongsTo(User::class, 'input_cashier');
    }

    public function outputCashier()
    {
        return $this->belongsTo(User::class, 'output_cashier');
    }
}
