<?php

namespace App\Services;

use App\Repositories\CashflowRepository;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    // Cache duration in seconds
    private const CACHE_DURATION = 300; // 5 minutes

    public function __construct(
        private CashflowRepository $cashflowRepository,
        private CashflowService $cashflowService
    ) {}

    /**
     * Get comprehensive cashflow report with caching
     */
    public function getCashflowReport(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        // Include cache version in the key to enable global invalidation
        $cacheVersion = Cache::get('cashflow_cache_version', 1);
        $cacheKey = "cashflow_report_v{$cacheVersion}_" . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($filters) {
            return $this->cashflowRepository->getCashflowSummary($filters);
        });
    }

    /**
     * Delete cashflow record
     */
    public function deleteCashflow(int $id): bool
    {
        $deleted = $this->cashflowService->deleteCashflow($id);

        if ($deleted) {
            // Clear related cache
            $this->clearCashflowCache();
        }

        return $deleted;
    }

    /**
     * Get cashflow report without caching (for real-time data)
     */
    public function getCashflowReportLive(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);
        return $this->cashflowRepository->getCashflowSummary($filters);
    }

    /**
     * Normalize and validate filters
     */
    private function normalizeFilters(array $filters): array
    {
        return [
            'from_date' => $filters['from_date'] ?? now()->format('Y-m-d'),
            'to_date' => $filters['to_date'] ?? now()->format('Y-m-d'),
            'warehouse_id' => $filters['warehouse_id'] ?? null,
            'user_id' => $filters['user_id'] ?? null,
        ];
    }

    /**
     * Clear all cashflow related cache efficiently
     * Uses version-based invalidation instead of deleting individual keys
     */
    private function clearCashflowCache(): void
    {
        // Instead of deleting all keys, we increment a version number
        // This effectively invalidates all existing cache entries without
        // having to scan and delete individual keys in Redis
        $currentVersion = Cache::get('cashflow_cache_version', 1);
        Cache::put('cashflow_cache_version', $currentVersion + 1, 86400 * 30); // 30 days

        // For tag-based cache drivers, we can still use tags
        if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
            Cache::tags(['cashflow_report'])->flush();
        }
    }

    /**
     * Get summary statistics for dashboard
     */
    public function getSummaryStats(array $filters): array
    {
        // We can use cached data for summary stats since we have proper invalidation now
        $data = $this->getCashflowReport($filters);

        return [
            'total_transactions' => $data['cashflows']->count(),
            'total_income' => $data['totalIn'],
            'total_expense' => $data['totalOut'],
            'net_flow' => $data['totalIn'] - $data['totalOut'],
            'opening_balance' => $data['awalValue'],
            'closing_balance' => $data['akhirValue'],
        ];
    }

    /**
     * Export cashflow data for reporting
     */
    public function exportCashflowData(array $filters, string $format = 'array'): array
    {
        // For exports, always use live data to ensure accuracy
        $data = $this->getCashflowReportLive($filters);

        switch ($format) {
            case 'csv':
                return $this->formatForCsv($data);
            case 'excel':
                return $this->formatForExcel($data);
            default:
                return $data;
        }
    }

    /**
     * Format data for CSV export
     */
    private function formatForCsv(array $data): array
    {
        $formatted = [];

        foreach ($data['cashflows'] as $cashflow) {
            $formatted[] = [
                'date' => $cashflow->created_at->format('Y-m-d H:i:s'),
                'description' => $cashflow->description,
                'type' => $cashflow->for,
                'in' => $cashflow->in,
                'out' => $cashflow->out,
                'payment_method' => $cashflow->payment_method,
                'user' => $cashflow->user->name ?? '',
            ];
        }

        return $formatted;
    }

    /**
     * Format data for Excel export
     */
    private function formatForExcel(array $data): array
    {
        // Similar to CSV but with additional formatting for Excel
        return $this->formatForCsv($data);
    }
}
