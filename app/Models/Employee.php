<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'nickname',
        'ktp',
        'warehouse_id',
        'isActive'
    ];

    protected $casts = [
        'isActive' => 'boolean',
    ];

    // Add scope for active employees
    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function cashAdvances(): HasMany
    {
        return $this->hasMany(CashAdvance::class);
    }

    public function salarySetting(): HasOne
    {
        return $this->hasOne(SalarySetting::class);
    }

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
