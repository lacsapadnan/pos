<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'warehouse_id',
        'approved_by',
        'advance_number',
        'amount',
        'advance_date',
        'type',
        'installment_count',
        'installment_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'description',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CashAdvancePayment::class);
    }

    // Helper methods
    public function isInstallment(): bool
    {
        return $this->type === 'installment';
    }

    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getProgressPercentage(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        return min(100, ($this->paid_amount / $this->amount) * 100);
    }

    public function generateAdvanceNumber(): string
    {
        $prefix = 'KSB';
        $date = now()->format('Ymd');
        $lastNumber = static::whereDate('created_at', today())
            ->where('advance_number', 'like', "{$prefix}-{$date}-%")
            ->count();

        return sprintf('%s-%s-%04d', $prefix, $date, $lastNumber + 1);
    }

    // Boot method to auto-generate advance number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cashAdvance) {
            if (empty($cashAdvance->advance_number)) {
                $cashAdvance->advance_number = $cashAdvance->generateAdvanceNumber();
            }

            // Set remaining amount initially
            $cashAdvance->remaining_amount = $cashAdvance->amount;

            // Calculate installment amount if installment type
            if ($cashAdvance->type === 'installment' && $cashAdvance->installment_count > 0) {
                $cashAdvance->installment_amount = $cashAdvance->amount / $cashAdvance->installment_count;
            }
        });
    }
}
