<?php

namespace App\Http\Controllers;

use App\Models\SellDetail;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Show dashboard page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userRoles = auth()->user()->getRoleNames();
        $isMaster = $userRoles->first() === 'master';

        // Determine warehouse ID for filtering
        if ($isMaster) {
            $warehouseId = $request->input('warehouse_id', $user->warehouse_id);
            $warehouses = Warehouse::orderBy('name', 'asc')->get();
        } else {
            $warehouseId = $user->warehouse_id;
            $warehouses = collect();
        }

        // Get top 10 selling products for the selected warehouse
        $topProducts = Cache::remember("top_products_$warehouseId", 300, function () use ($warehouseId) {
            return SellDetail::select(
                'products.name as product_name',
                DB::raw('SUM(sell_details.quantity) as total_sold')
            )
                ->join('sells', 'sell_details.sell_id', '=', 'sells.id')
                ->join('products', 'sell_details.product_id', '=', 'products.id')
                ->where('sells.warehouse_id', $warehouseId)
                ->groupBy('sell_details.product_id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();
        });

        return view('pages.dashboard', compact('topProducts', 'warehouses', 'isMaster', 'warehouseId'));
    }
}
