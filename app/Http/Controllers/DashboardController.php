<?php

namespace App\Http\Controllers;

use App\Models\SellDetail;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get more than 10 to account for possible null products, then filter
        $topProducts = SellDetail::select('product_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->take(20) // get more than needed to filter out nulls
            ->get()
            ->filter(function ($item) {
                return $item->product !== null;
            })
            ->take(10)
            ->values();

        return view('pages.dashboard', compact('topProducts'));
    }
}
