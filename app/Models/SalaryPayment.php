<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'warehouse_id',
        'salary_setting_id',
        'period_start',
        'period_end',
        'daily_salary',
        'monthly_salary',
        'total_work_days',
        'present_days',
        'total_work_hours',
        'gross_salary',
        'cash_advance_deduction',
        'other_deductions',
        'net_salary',
        'status',
        'notes',
        'calculated_by',
        'approved_by',
        'calculated_at',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'daily_salary' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'total_work_hours' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'cash_advance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function salarySetting(): BelongsTo
    {
        return $this->belongsTo(SalarySetting::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper methods for status checking
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCalculated(): bool
    {
        return $this->status === 'calculated';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    // Calculate salary based on attendance and cash advance
    public function calculateSalary(): void
    {
        // Copy current salary rates from settings
        $this->daily_salary = $this->salarySetting->daily_salary;
        $this->monthly_salary = $this->salarySetting->monthly_salary;

        // Get attendance data for the period
        $attendances = Attendance::where('user_id', $this->employee->user_id)
            ->whereBetween('check_in', [$this->period_start, $this->period_end])
            ->get();

        // Calculate present days and total work hours
        $this->present_days = $attendances->count();
        $this->total_work_hours = $attendances->sum(function ($attendance) {
            return $attendance->getTotalWorkHours();
        });

        // Calculate total work days in period (excluding weekends if needed)
        $start = Carbon::parse($this->period_start);
        $end = Carbon::parse($this->period_end);
        $this->total_work_days = $start->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday(); // Only count weekdays
        }, $end) + 1;

        // Calculate gross salary
        if ($this->monthly_salary > 0) {
            // Use monthly salary as base
            $this->gross_salary = $this->monthly_salary;
        } elseif ($this->daily_salary > 0) {
            // Calculate based on daily salary and present days
            $this->gross_salary = $this->daily_salary * $this->present_days;
        }

        // Calculate cash advance deductions for this period
        $cashAdvanceDeduction = 0;

        // Get all approved cash advances for this employee
        $cashAdvances = CashAdvance::where('employee_id', $this->employee_id)
            ->where('status', 'approved')
            ->get();

        foreach ($cashAdvances as $cashAdvance) {
            if ($cashAdvance->type === 'direct') {
                // For direct advances, deduct full amount only if advance date is within salary period
                if ($cashAdvance->advance_date >= $this->period_start && $cashAdvance->advance_date <= $this->period_end) {
                    $cashAdvanceDeduction += $cashAdvance->amount;
                }
            } elseif ($cashAdvance->type === 'installment') {
                // For installment advances, deduct installment payments due within this period
                $installmentPayments = $cashAdvance->payments()
                    ->where('status', 'pending')
                    ->whereBetween('due_date', [$this->period_start, $this->period_end])
                    ->sum('amount');

                $cashAdvanceDeduction += $installmentPayments;
            }
        }

        $this->cash_advance_deduction = $cashAdvanceDeduction;

        // Calculate net salary
        $this->net_salary = $this->gross_salary - $this->cash_advance_deduction - $this->other_deductions;

        // Update status and calculated info
        $this->status = 'calculated';
        $this->calculated_by = auth()->id();
        $this->calculated_at = now();

        $this->save();
    }

    // Get attendance efficiency percentage
    public function getAttendancePercentage(): float
    {
        if ($this->total_work_days <= 0) {
            return 0;
        }
        return min(100, ($this->present_days / $this->total_work_days) * 100);
    }

    // Generate salary period string
    public function getPeriodString(): string
    {
        return $this->period_start->format('d M Y') . ' - ' . $this->period_end->format('d M Y');
    }

    // Get status color for UI
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'calculated' => 'info',
            'approved' => 'success',
            'paid' => 'primary',
            default => 'secondary'
        };
    }

    // Get status label
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'calculated' => 'Calculated',
            'approved' => 'Approved',
            'paid' => 'Paid',
            default => 'Unknown'
        };
    }
}
