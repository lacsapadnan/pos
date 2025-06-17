<?php

namespace App\Http\Controllers;

use App\Models\SellDetail;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show dashboard page.
     * No heavy data loaded here.
     */
    public function index()
    {
        return view('pages.dashboard');
    }

    /**
     * API for Top 10 Produk Terlaris Chart.
     * This returns JSON for async Chart.js.
     */
    public function topProducts()
    {
        $warehouse_id = auth()->user()->warehouse_id;

        $topProducts = SellDetail::select('product_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->whereHas('sell', function ($query) use ($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->take(20)
            ->get()
            ->filter(fn($item) => $item->product !== null)
            ->take(10)
            ->values();

        return response()->json($topProducts);
    }
}