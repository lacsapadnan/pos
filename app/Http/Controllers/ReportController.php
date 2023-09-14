<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;

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
        $warehouse = $request->input('warehouse');
        $user_id = $request->input('user_id'); // Add user_id input

        $query = Cashflow::orderBy('created_at', 'desc')->with('user');

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $query->where('user_id', $user_id); // Filter by user_id if provided
        }

        $cashflow = $query->get();

        return response()->json($cashflow);
    }
}
