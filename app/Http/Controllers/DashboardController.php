<?php

namespace App\Http\Controllers;

use App\Models\SellDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Show dashboard page.
     */
    public function index()
    {
        return view('pages.dashboard');
    }

    /**
     * API for Top 10 Produk Terlaris Chart.
     * Returns JSON for Chart.js.
     */
    public function topProducts()
    {
        $warehouseId = auth()->user()->warehouse_id;

        // Gunakan Cache opsional 5 menit
        $topProducts = Cache::remember("top_products_$warehouseId", 300, function () use ($warehouseId) {
            return SellDetail::select('sell_details.product_id', DB::raw('SUM(sell_details.quantity) as total_sold'))
                ->join('sells', 'sell_details.sell_id', '=', 'sells.id')
                ->join('products', 'sell_details.product_id', '=', 'products.id')
                ->where('sells.warehouse_id', $warehouseId)
                ->groupBy('sell_details.product_id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get([
                    'products.name as product_name',
                    DB::raw('SUM(sell_details.quantity) as total_sold')
                ]);
        });

        return response()->json($topProducts);
    }
}