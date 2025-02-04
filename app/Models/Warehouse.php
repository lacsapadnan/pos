<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'address', 'phone'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'inventories')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
