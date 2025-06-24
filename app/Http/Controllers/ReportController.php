<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CashflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.report.index', compact('warehouses', 'users'));
    }

    public function data(Request $request)
    {
        $role = auth()->user()->getRoleNames()->first();
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date') ?? now()->format('Y-m-d');
        $toDate = $request->input('to_date') ?? now()->format('Y-m-d');

        $endDate = Carbon::parse($toDate)->endOfDay();

        $query = Cashflow::with('user')->orderBy('created_at', 'desc');

        if ($role == 'master') {
            $warehouse = $request->input('warehouse');
        } else {
            $warehouse = auth()->user()->warehouse_id;
            $query->where('user_id', auth()->user()->id)->where('warehouse_id', $warehouse);
        }

        $query->when($warehouse, function ($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse);
        })
            ->when($user_id, function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })
            ->whereBetween('created_at', [$fromDate, $endDate]);

        // Calculating awalValue only once
        $awalQuery = Cashflow::whereDate('created_at', '<', $fromDate);

        if ($role != 'master') {
            $awalQuery->where('user_id', auth()->user()->id)->where('warehouse_id', $warehouse);
        }

        $awalQuery->when($warehouse, function ($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse);
        })
            ->when($user_id, function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            });

        $awalValue = $awalQuery->sum('in') - $awalQuery->sum('out');

        $cashflows = $query->get();
        $sumIn = $cashflows->sum('in');
        $sumOut = $cashflows->sum('out');
        $akhirValue = $awalValue + ($sumIn - $sumOut);

        $response = [
            'cashflow' => $cashflows,
            'awalValue' => $awalValue,
            'akhirValue' => $akhirValue,
        ];

        return response()->json($response);
    }

    public function destroy($id)
    {
        $cashflowService = app(CashflowService::class);

        if ($cashflowService->deleteCashflow($id)) {
            return redirect()->route('report.index')->with('success', 'Data berhasil dihapus!');
        }

        return redirect()->route('report.index')->with('error', 'Data tidak ditemukan!');
    }
}
