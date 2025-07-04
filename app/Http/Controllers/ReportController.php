<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AuthorizationService;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private AuthorizationService $authorizationService
    ) {}

    /**
     * Display the report index page
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        $users = User::all();

        return view('pages.report.index', compact('warehouses', 'users'));
    }

    /**
     * Get report data with filters
     */
    public function data(ReportRequest $request)
    {
        $filters = $request->getFilters();
        $authorizedFilters = $this->authorizationService->getAuthorizedFilters($filters);

        $report = $this->reportService->getCashflowReport($authorizedFilters);

        return response()->json($report);
    }

    /**
     * Delete a cashflow record
     */
    public function destroy(int $id)
    {
        if ($this->reportService->deleteCashflow($id)) {
            return redirect()->route('laporan')->with('success', 'Data berhasil dihapus!');
        }

        return redirect()->route('laporan')->with('error', 'Data tidak ditemukan!');
    }

    /**
     * Get live data without caching (for real-time updates)
     */
    public function liveData(ReportRequest $request)
    {
        $filters = $request->getFilters();
        $authorizedFilters = $this->authorizationService->getAuthorizedFilters($filters);

        $report = $this->reportService->getCashflowReportLive($authorizedFilters);

        return response()->json($report);
    }

    /**
     * Get summary statistics
     */
    public function summary(ReportRequest $request)
    {
        $filters = $request->getFilters();
        $authorizedFilters = $this->authorizationService->getAuthorizedFilters($filters);

        $stats = $this->reportService->getSummaryStats($authorizedFilters);

        return response()->json($stats);
    }

    /**
     * Export report data
     */
    public function export(ReportRequest $request)
    {
        $filters = $request->getFilters();
        $authorizedFilters = $this->authorizationService->getAuthorizedFilters($filters);
        $format = $request->input('format', 'csv');

        $data = $this->reportService->exportCashflowData($authorizedFilters, $format);

        $filename = 'cashflow_report_' . date('Y-m-d_H-i-s') . '.' . $format;

        switch ($format) {
            case 'csv':
                return response()->streamDownload(function () use ($data) {
                    $file = fopen('php://output', 'w');

                    // Add CSV headers
                    fputcsv($file, ['Date', 'Description', 'Type', 'In', 'Out', 'Payment Method', 'User']);

                    foreach ($data as $row) {
                        fputcsv($file, $row);
                    }

                    fclose($file);
                }, $filename, ['Content-Type' => 'text/csv']);

            default:
                return response()->json($data);
        }
    }
}
