<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'mutation_id',
        'cashier_id',
        'total_received',
        'outstanding',
        'to_treasury'
    ];

    protected $casts = [
        'total_received' => 'decimal:2',
        'outstanding' => 'decimal:2',
    ];

    public function mutation()
    {
        return $this->belongsTo(TreasuryMutation::class, 'mutation_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
