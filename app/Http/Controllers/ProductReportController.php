<?php

namespace App\Http\Controllers;

use App\Models\ProductReport;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.product.report', compact('warehouses', 'users'));
    }

    public function data(Request $request)
    {
        $role = auth()->user()->getRoleNames();
        $user_id = $request->input('user_id');
        // $selectedMonth = $request->input('selected_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $for = $request->input('for');

        $defaultDate = now()->format('Y-m-d');

        if (!$fromDate) {
            $fromDate = $defaultDate;
        }

        if (!$toDate) {
            $toDate = $defaultDate;
        }

        if ($role[0] == 'master') {
            $warehouse = $request->input('warehouse');
            $query = ProductReport::orderBy('created_at', 'desc')->with('user', 'supplier', 'customer', 'product');
        } else {
            $warehouse = auth()->user()->warehouse_id;
            $query = ProductReport::where('user_id', auth()->user()->id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->with('user', 'supplier', 'customer', 'product')
                ->orderBy('created_at', 'desc');
        }

        // $query->whereYear('created_at', '=', Carbon::parse($selectedMonth)->year)
        //     ->whereMonth('created_at', '=', Carbon::parse($selectedMonth)->month);

        $query->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($for) {
            $query->where('for', $for);
        }

        if ($role[0] == 'master') {
            $totalNilai = ProductReport::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->when($warehouse, function ($query) use ($warehouse) {
                    return $query->where('warehouse_id', $warehouse);
                })
                ->when($user_id, function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })
                ->when($for, function ($query) use ($for) {
                    return $query->where('for', $for);
                })
                ->sum(DB::raw('qty * price'));

            $totalDus = ProductReport::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->when($warehouse, function ($query) use ($warehouse) {
                    return $query->where('warehouse_id', $warehouse);
                })
                ->when($user_id, function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })
                ->when($for, function ($query) use ($for) {
                    return $query->where('for', $for);
                })
                ->where('unit_type', 'DUS')
                ->sum('qty');

            $totalPak = ProductReport::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->when($warehouse, function ($query) use ($warehouse) {
                    return $query->where('warehouse_id', $warehouse);
                })
                ->when($user_id, function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })
                ->when($for, function ($query) use ($for) {
                    return $query->where('for', $for);
                })
                ->where('unit_type', 'PAK')
                ->sum('qty');

            $totalEceran = ProductReport::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->when($warehouse, function ($query) use ($warehouse) {
                    return $query->where('warehouse_id', $warehouse);
                })
                ->when($user_id, function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })
                ->when($for, function ($query) use ($for) {
                    return $query->where('for', $for);
                })
                ->where('unit_type', 'ECERAN')
                ->sum('qty');
        } else {
            $totalNilai = ProductReport::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('user_id', auth()->user()->id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('for', $for)
                ->sum(DB::raw('qty * price'));

            $totalDus = ProductReport::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('user_id', auth()->user()->id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('for', $for)
                ->where('unit_type', 'DUS')
                ->sum('qty');

            $totalPak = ProductReport::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('user_id', auth()->user()->id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('for', $for)
                ->where('unit_type', 'PAK')
                ->sum('qty');

            $totalEceran = ProductReport::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('user_id', auth()->user()->id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('for', $for)
                ->where('unit_type', 'ECERAN')
                ->sum('qty');
        }

        $response = [
            'report' => $query->get(),
            'totalNilai' => $totalNilai,
            'totalDus' => $totalDus,
            'totalPak' => $totalPak,
            'totalEceran' => $totalEceran,
        ];


        return response()->json($response);
    }
}
