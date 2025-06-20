<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendStock extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'from_warehouse',
        'to_warehouse',
        'status',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse');
    }

    public function sendStockDetails()
    {
        return $this->hasMany(SendStockDetail::class);
    }

    // Scope for draft records
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Scope for completed records
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
