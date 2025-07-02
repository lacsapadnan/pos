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
        'status',
        'notes'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
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

        return round($totalMinutes / 60, 2);
    }
}
