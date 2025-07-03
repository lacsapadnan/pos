<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Kas extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'kas_income_item_id',
        'kas_expense_item_id',
        'warehouse_id',
        'invoice',
        'date',
        'type',
        'amount',
        'description',
    ];

    public function kas_income_item()
    {
        return $this->belongsTo(KasIncomeItem::class);
    }

    public function kas_expense_item()
    {
        return $this->belongsTo(KasExpenseItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice', 'date', 'type', 'amount', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('kas_management')
            ->setDescriptionForEvent(fn(string $eventName) => "Kas transaction {$this->invoice} has been {$eventName}");
    }
}
