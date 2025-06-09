<?php

namespace App\Http\Controllers;

use App\Models\SellDetail;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $topProducts = SellDetail::select('product_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->take(10)
            ->get();

        return view('pages.dashboard', compact('topProducts'));
    }
}
