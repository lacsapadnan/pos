<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Settlement extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['total_received', 'outstanding', 'to_treasury'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('settlement_management')
            ->setDescriptionForEvent(fn(string $eventName) => "Settlement #{$this->id} has been {$eventName}");
    }
}
