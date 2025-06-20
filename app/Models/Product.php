<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable =
    [
        'group',
        'name',
        'unit_dus',
        'unit_pak',
        'unit_eceran',
        'barcode_dus',
        'barcode_pak',
        'barcode_eceran',
        'dus_to_eceran',
        'pak_to_eceran',
        'price_dus',
        'price_pak',
        'price_eceran',
        'sales_price',
        'lastest_price_eceran',
        'hadiah',
        'price_sell_dus',
        'price_sell_pak',
        'price_sell_eceran',
        'promo',
        'lastest_price_eceran_out_of_town',
        'price_sell_dus_out_of_town',
        'price_sell_pak_out_of_town',
        'price_sell_eceran_out_of_town',
        'isShow',
    ];

    public function unit_dus()
    {
        return $this->belongsTo(Unit::class, 'unit_dus');
    }

    public function unit_pak()
    {
        return $this->belongsTo(Unit::class, 'unit_pak');
    }

    public function unit_eceran()
    {
        return $this->belongsTo(Unit::class, 'unit_eceran');
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'inventories')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function getIsShowAttribute($value)
    {
        return (bool) $value;
    }
}
