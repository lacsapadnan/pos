<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'address', 'phone', 'isOutOfTown'];

    public function getIsOutOfTownAttribute($value)
    {
        return (bool) $value;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'inventories')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
