<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Retur extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'sell_id',
        'product_id',
        'unit_id',
        'warehouse_id',
        'qty',
        'retur_date'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'sell_id',
                'product_id',
                'unit_id',
                'warehouse_id',
                'qty',
                'retur_date',
                'sell.invoice',
                'product.name',
                'unit.name',
                'warehouse.name'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('retur');
    }

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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
