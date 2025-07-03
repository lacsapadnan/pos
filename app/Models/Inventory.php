<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Inventory extends Model
{
    use HasFactory;
    protected $fillable =
    [
        'product_id',
        'warehouse_id',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logFillable()
    //         ->logOnlyDirty()
    //         ->dontSubmitEmptyLogs()
    //         ->useLogName('inventory_management')
    //         ->setDescriptionForEvent(fn(string $eventName) => "Inventory for {$this->product->name} at {$this->warehouse->name} has been {$eventName}")
    //         ->logOnly(['quantity'])
    //         ->dontLogIfAttributesChangedOnly(['quantity'], function (Inventory $inventory) {
    //             // Get the current URL to determine if we're in a sell route
    //             $currentUrl = request()->url();
    //             return str_contains($currentUrl, '/sell/');
    //         });
    // }
}
