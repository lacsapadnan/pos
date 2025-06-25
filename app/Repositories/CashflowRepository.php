<?php

namespace App\Repositories;

use App\Models\Cashflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CashflowRepository
{
    /**
     * Get cashflows with filters applied
     */
    public function getCashflowsByFilters(array $filters): Collection
    {
        $query = Cashflow::with('user')->orderBy('created_at', 'desc');

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get comprehensive cashflow summary with optimized single query
     */
    public function getCashflowSummary(array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = Carbon::parse($filters['to_date'])->endOfDay();

        // Single optimized query for summary calculations with proper parameter binding
        $query = DB::table('cashflows')
            ->selectRaw('
                SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN `in` ELSE 0 END) as current_in,
                SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN `out` ELSE 0 END) as current_out,
                SUM(CASE WHEN DATE(created_at) < ? THEN `in` ELSE 0 END) as before_in,
                SUM(CASE WHEN DATE(created_at) < ? THEN `out` ELSE 0 END) as before_out
            ', [
                $fromDate,
                $toDate,
                $fromDate,
                $toDate,
                $fromDate,
                $fromDate
            ]);

        // Apply filters
        if ($filters['warehouse_id'] ?? null) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if ($filters['user_id'] ?? null) {
            $query->where('user_id', $filters['user_id']);
        }

        $summaryData = $query->first();

        // Get detailed cashflows for the period
        $cashflows = $this->buildBaseQuery($filters, false)->get();

        $awalValue = $summaryData->before_in - $summaryData->before_out;
        $akhirValue = $awalValue + ($summaryData->current_in - $summaryData->current_out);

        return [
            'cashflows' => $cashflows,
            'awalValue' => $awalValue,
            'akhirValue' => $akhirValue,
            'totalIn' => $summaryData->current_in,
            'totalOut' => $summaryData->current_out,
        ];
    }

    /**
     * Build base query with filters
     */
    private function buildBaseQuery(array $filters, bool $beforeDate = false): Builder
    {
        $query = Cashflow::with('user')->orderBy('created_at', 'desc');

        if ($beforeDate) {
            $query->whereDate('created_at', '<', $filters['from_date']);
        } else {
            $endDate = Carbon::parse($filters['to_date'])->endOfDay();
            $query->whereBetween('created_at', [$filters['from_date'], $endDate]);
        }

        return $this->applyFilters($query, $filters);
    }

    /**
     * Apply common filters to query
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query->when($filters['warehouse_id'] ?? null, fn($q, $id) => $q->where('warehouse_id', $id))
            ->when($filters['user_id'] ?? null, fn($q, $id) => $q->where('user_id', $id));
    }

    /**
     * Get balance before specified date
     */
    public function getBalanceBeforeDate(string $date, array $filters = []): float
    {
        $query = Cashflow::whereDate('created_at', '<', $date);

        $this->applyFilters($query, $filters);

        return $query->sum('in') - $query->sum('out');
    }

    /**
     * Get cashflow totals for date range
     */
    public function getTotalsForDateRange(string $fromDate, string $toDate, array $filters = []): array
    {
        $endDate = Carbon::parse($toDate)->endOfDay();

        $query = Cashflow::whereBetween('created_at', [$fromDate, $endDate]);
        $this->applyFilters($query, $filters);

        return [
            'total_in' => $query->sum('in'),
            'total_out' => $query->sum('out'),
        ];
    }
}
