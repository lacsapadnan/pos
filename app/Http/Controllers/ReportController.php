<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\User;
use App\Models\Warehouse;
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
        $role = auth()->user()->getRoleNames();
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $defaultDate = now()->format('Y-m-d');

        if (!$fromDate) {
            $fromDate = $defaultDate;
        }

        if (!$toDate) {
            $toDate = $defaultDate;
        }

        if ($role[0] == 'master') {
            $warehouse = $request->input('warehouse');
            $query = Cashflow::orderBy('created_at', 'desc')->with('user');
        } else {
            $warehouse = auth()->user()->warehouse_id;
            $query = Cashflow::where('user_id', auth()->user()->id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->with('user')
                ->orderBy('created_at', 'desc');
        }

        // Query untuk mendapatkan data Cashflow sesuai kriteria.
        $query = $query->when($warehouse, function ($query) use ($warehouse) {
            return $query->where('warehouse_id', $warehouse);
        })
            ->when($user_id, function ($query) use ($user_id) {
                return $query->where('user_id', $user_id);
            })
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $endDate = Carbon::parse($toDate)->endOfDay();

                return $query->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $endDate);
            });

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($fromDate && $toDate) {
            $endDate = Carbon::parse($toDate)->endOfDay();

            $query->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($role[0] == 'master') {
            $awalValue = Cashflow::whereDate('created_at', '<', $fromDate)
                ->when($warehouse, function ($query) use ($warehouse) {
                    return $query->where('warehouse_id', $warehouse);
                })
                ->when($user_id, function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })
                ->sum('in') - Cashflow::whereDate('created_at', '<', $fromDate)
                ->when($warehouse, function ($query) use ($warehouse) {
                    return $query->where('warehouse_id', $warehouse);
                })
                ->when($user_id, function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })
                ->sum('out');
        } else {
            $awalValue = Cashflow::whereDate('created_at', '<', $fromDate)
                ->where('user_id', auth()->user()->id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->sum('in') - Cashflow::whereDate('created_at', '<', $fromDate)
                ->where('user_id', auth()->user()->id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->sum('out');
        }

        // Hitung nilai "akhir".
        $akhirValue = $awalValue + ($query->sum('in') - $query->sum('out'));

        $response = [
            'cashflow' => $query->get(),
            'awalValue' => $awalValue,
            'akhirValue' => $akhirValue,
        ];

        return response()->json($response);
    }
    
    public function destroy($id) {
        $cashflow = Cashflow::findOrFail($id);
        $cashflow->delete();

        return redirect()->route('report.index')->with('success', 'Data berhasil dihapus!');
    }
}
