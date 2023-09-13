<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('pages.report.index', compact('warehouses'));
    }

    public function data(Request $request)
    {
        $warehouse = $request->input('warehouse');

        if ($warehouse) {
            $cashflow = Cashflow::orderBy('created_at', 'desc')->where('warehouse_id', $warehouse)->get();
        } else {
            $cashflow = Cashflow::orderBy('created_at', 'desc')->get();
        }

        return response()->json($cashflow);
    }
}
