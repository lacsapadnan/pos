<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'warehouse_id',
        'check_in',
        'check_out',
        'break_start',
        'break_end',
        'status',
        'notes'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Check if user can check out (minimum 1 hour after check in)
     */
    public function canCheckOut(): bool
    {
        if (!$this->check_in) {
            return false;
        }

        return $this->check_in->diffInHours(now()) >= 1;
    }

    /**
     * Get total work hours for the day
     */
    public function getTotalWorkHours(): float
    {
        if (!$this->check_in) {
            return 0;
        }

        $checkOut = $this->check_out ?? now();
        $totalMinutes = $this->check_in->diffInMinutes($checkOut);

        // Subtract break time if any
        if ($this->break_start && $this->break_end) {
            $breakMinutes = $this->break_start->diffInMinutes($this->break_end);
            $totalMinutes -= $breakMinutes;
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Check if user is currently on break
     */
    public function isOnBreak(): bool
    {
        return $this->status === 'on_break' && $this->break_start && !$this->break_end;
    }

    /**
     * Check if user already took a break today
     */
    public function hasUsedBreak(): bool
    {
        return $this->break_start !== null;
    }
}
