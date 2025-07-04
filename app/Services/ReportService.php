<?php

namespace App\Services;

use App\Repositories\CashflowRepository;

class ReportService
{
    public function __construct(
        private CashflowRepository $cashflowRepository,
        private CashflowService $cashflowService
    ) {}

    /**
     * Get comprehensive cashflow report
     */
    public function getCashflowReport(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);
        return $this->cashflowRepository->getCashflowSummary($filters);
    }

    /**
     * Delete cashflow record
     */
    public function deleteCashflow(int $id): bool
    {
        return $this->cashflowService->deleteCashflow($id);
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
     * Get summary statistics for dashboard
     */
    public function getSummaryStats(array $filters): array
    {
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
