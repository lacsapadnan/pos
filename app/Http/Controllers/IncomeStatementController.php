<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Sell;
use App\Models\SellDetail;
use App\Models\SellRetur;
use App\Models\SellReturDetail;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeStatementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:baca laba rugi');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.income-statement.index', compact('warehouses', 'users'));
    }

    /**
     * Clear cache for income statement data
     */
    public function clearCache(Request $request)
    {
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date') ?? now()->format('Y-m-d');
        $toDate = $request->input('to_date') ?? now()->format('Y-m-d');
        $warehouse_id = $request->input('warehouse');

        $cacheKey = "income_statement_" . md5(serialize([
            'user_id' => $user_id,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'warehouse_id' => $warehouse_id,
            'auth_user' => auth()->id()
        ]));

        cache()->forget($cacheKey);

        return response()->json(['message' => 'Cache cleared successfully']);
    }

    public function data(Request $request)
    {
        // Increase memory limit and execution time for large datasets
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date') ?? now()->format('Y-m-d');
        $toDate = $request->input('to_date') ?? now()->format('Y-m-d');
        $warehouse_id = $request->input('warehouse');

        // Check if user can see all income statement data
        if (!auth()->user()->hasPermissionTo('lihat semua laba rugi')) {
            // Restrict to user's own warehouse and user data
            $warehouse_id = auth()->user()->warehouse_id;
            $user_id = auth()->id();
        }

        $endDate = Carbon::parse($toDate)->endOfDay();

        // Create cache key for this specific request
        $cacheKey = "income_statement_" . md5(serialize([
            'user_id' => $user_id,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'warehouse_id' => $warehouse_id,
            'auth_user' => auth()->id()
        ]));

        // Try to get cached result first (cache for 5 minutes)
        $cachedResult = cache()->get($cacheKey);
        if ($cachedResult) {
            return response()->json($cachedResult);
        }

        try {
            // Get warehouse info to determine if it's out of town
            $warehouse = null;
            if ($warehouse_id) {
                $warehouse = Warehouse::select('id', 'name', 'isOutOfTown')->find($warehouse_id);
            }

            // Calculate all data with optimized methods
            $salesData = $this->calculateSalesRevenueOptimized($fromDate, $endDate, $warehouse_id, $user_id);
            $cogsData = $this->calculateCostOfGoodsSoldOptimized($fromDate, $endDate, $warehouse_id, $user_id, $warehouse);
            $operatingExpenses = $this->calculateOperatingExpensesOptimized($fromDate, $endDate, $warehouse_id, $user_id);
            $otherIncome = $this->calculateOtherIncomeOptimized($fromDate, $endDate, $warehouse_id, $user_id);

            // Synchronize product lists and ensure consistent ordering
            $this->synchronizeProductData($salesData, $cogsData);

            // Calculate totals with safe numeric operations
            $totalRevenue = floatval($salesData['total_revenue'] ?? 0);
            $totalCogs = floatval($cogsData['total_cogs'] ?? 0);
            $totalOtherIncome = floatval($otherIncome['total_other_income'] ?? 0);
            $totalOperatingExpenses = floatval($operatingExpenses['total_operating_expenses'] ?? 0);

            $grossProfit = $totalRevenue - $totalCogs;
            $netIncome = $grossProfit + $totalOtherIncome - $totalOperatingExpenses;

            $response = [
                'sales_data' => $salesData,
                'cogs_data' => $cogsData,
                'operating_expenses' => $operatingExpenses,
                'other_income' => $otherIncome,
                'gross_profit' => $grossProfit,
                'net_income' => $netIncome,
                'period' => [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'warehouse' => $warehouse ? $warehouse->name : 'Semua Gudang'
                ],
                'cache_generated_at' => now()->toISOString()
            ];

            // Cache the result for 5 minutes
            cache()->put($cacheKey, $response, 300);

            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Income Statement calculation error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate income statement. Please try with a smaller date range.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateSalesRevenueOptimized($fromDate, $endDate, $warehouse_id, $user_id)
    {
        // Step 1: Get total revenue and count using raw SQL for better performance
        $totalQuery = DB::table('sells')
            ->select(DB::raw('SUM(CAST(grand_total as DECIMAL(15,2))) as total_revenue, COUNT(*) as total_transactions'))
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'batal')
            ->where('status', '!=', 'piutang')
            ->whereBetween('created_at', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $totalQuery->where('warehouse_id', $warehouse_id);
        }

        if ($user_id) {
            $totalQuery->where('cashier_id', $user_id);
        }

        $totals = $totalQuery->first();
        $totalRevenue = floatval($totals->total_revenue ?? 0);
        $totalTransactions = intval($totals->total_transactions ?? 0);

        // Step 2: Get sales by product using optimized query
        $salesByProduct = [];

        // Use chunking to avoid memory issues with large datasets
        $sellQuery = Sell::select('id', 'grand_total')
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'batal')
            ->where('status', '!=', 'piutang')
            ->whereBetween('created_at', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $sellQuery->where('warehouse_id', $warehouse_id);
        }

        if ($user_id) {
            $sellQuery->where('cashier_id', $user_id);
        }

        $sellIds = $sellQuery->pluck('id')->toArray();

        if (!empty($sellIds)) {
            // Process sell details in chunks to avoid memory issues
            $chunkSize = 1000;
            $sellIdChunks = array_chunk($sellIds, $chunkSize);

            foreach ($sellIdChunks as $chunk) {
                $salesDetails = DB::table('sell_details as sd')
                    ->join('products as p', 'sd.product_id', '=', 'p.id')
                    ->select(
                        'sd.product_id',
                        'p.name as product_name',
                        'sd.quantity',
                        'sd.price',
                        'sd.diskon',
                        'sd.sell_id'
                    )
                    ->whereIn('sd.sell_id', $chunk)
                    ->get();

                foreach ($salesDetails as $detail) {
                    $productId = $detail->product_id;
                    $quantity = floatval($detail->quantity ?? 0);
                    $price = floatval($detail->price ?? 0);
                    $diskon = floatval($detail->diskon ?? 0);
                    $revenue = ($price * $quantity) - $diskon;

                    if (!isset($salesByProduct[$productId])) {
                        $salesByProduct[$productId] = [
                            'product_name' => $detail->product_name ?? 'Unknown Product',
                            'quantity_sold' => 0,
                            'total_revenue' => 0
                        ];
                    }

                    $salesByProduct[$productId]['quantity_sold'] += $quantity;
                    $salesByProduct[$productId]['total_revenue'] += $revenue;
                }

                // Free memory after processing each chunk
                unset($salesDetails);
            }
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'sales_by_product' => $salesByProduct,
            'sales_details' => [] // Don't return full details to save memory
        ];
    }

    private function calculateCostOfGoodsSoldOptimized($fromDate, $endDate, $warehouse_id, $user_id, $warehouse)
    {
        $totalCogs = 0;
        $cogsByProduct = [];
        $isOutOfTown = $warehouse ? ($warehouse->isOutOfTown ?? false) : false;

        // Use raw SQL with chunking for better performance
        // First, get the sell IDs that are not draft, batal, or piutang
        $validSellIds = DB::table('sells')
            ->select('id')
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'batal')
            ->where('status', '!=', 'piutang')
            ->whereBetween('created_at', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $validSellIds->where('warehouse_id', $warehouse_id);
        }

        if ($user_id) {
            $validSellIds->where('cashier_id', $user_id);
        }

        $validSellIdsArray = $validSellIds->pluck('id')->toArray();

        if (empty($validSellIdsArray)) {
            return [
                'total_cogs' => 0,
                'cogs_by_product' => [],
                'is_out_of_town' => $isOutOfTown
            ];
        }

        // Now get product reports only for valid sell transactions
        $query = DB::table('product_reports as pr')
            ->join('products as p', 'pr.product_id', '=', 'p.id')
            ->select(
                'pr.product_id',
                'p.name as product_name',
                'pr.qty',
                'pr.unit_type',
                'p.dus_to_eceran',
                'p.pak_to_eceran',
                'p.lastest_price_eceran',
                'p.lastest_price_eceran_out_of_town'
            )
            ->where('pr.for', 'KELUAR')
            ->where('pr.type', 'PENJUALAN')
            ->whereIn('pr.reference_id', $validSellIdsArray)
            ->whereBetween('pr.created_at', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $query->where('pr.warehouse_id', $warehouse_id);
        }

        if ($user_id) {
            $query->where('pr.user_id', $user_id);
        }

        // Process in chunks to avoid memory issues
        $query->orderBy('pr.id')->chunk(1000, function ($soldProducts) use (&$totalCogs, &$cogsByProduct, $isOutOfTown) {
            foreach ($soldProducts as $soldProduct) {
                $quantitySold = floatval($soldProduct->qty ?? 0);

                // Skip if no quantity sold
                if ($quantitySold <= 0) continue;

                // Convert quantity to eceran
                $quantityEceran = $this->convertQuantityToEceranFromData($quantitySold, $soldProduct->unit_type, $soldProduct);

                // Skip if converted quantity is 0
                if ($quantityEceran <= 0) continue;

                // Determine cost price based on warehouse location
                $costPrice = 0;
                if ($isOutOfTown) {
                    $costPrice = floatval($soldProduct->lastest_price_eceran_out_of_town ?? $soldProduct->lastest_price_eceran ?? 0);
                } else {
                    $costPrice = floatval($soldProduct->lastest_price_eceran ?? 0);
                }

                $productCogs = $quantityEceran * $costPrice;
                $totalCogs += $productCogs;

                $productId = $soldProduct->product_id;
                if (!isset($cogsByProduct[$productId])) {
                    $cogsByProduct[$productId] = [
                        'product_name' => $soldProduct->product_name ?? 'Unknown Product',
                        'quantity_sold_eceran' => 0,
                        'cost_price' => $costPrice,
                        'total_cogs' => 0
                    ];
                }

                $cogsByProduct[$productId]['quantity_sold_eceran'] += $quantityEceran;
                $cogsByProduct[$productId]['total_cogs'] += $productCogs;
            }
        });

        return [
            'total_cogs' => $totalCogs,
            'cogs_by_product' => $cogsByProduct,
            'is_out_of_town' => $isOutOfTown
        ];
    }

    private function calculateOperatingExpensesOptimized($fromDate, $endDate, $warehouse_id, $user_id)
    {
        // Get total expenses using raw SQL
        $totalQuery = DB::table('kas')
            ->select(DB::raw('SUM(CAST(amount as DECIMAL(15,2))) as total_expenses'))
            ->where('type', 'Kas Keluar')
            ->whereBetween('date', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $totalQuery->where('warehouse_id', $warehouse_id);
        }

        $totalExpenses = floatval($totalQuery->first()->total_expenses ?? 0);

        // Get expenses by category using optimized query
        $categoryQuery = DB::table('kas as k')
            ->leftJoin('kas_expense_items as kei', 'k.kas_expense_item_id', '=', 'kei.id')
            ->select(
                DB::raw('COALESCE(kei.name, "Lainnya") as category'),
                DB::raw('SUM(CAST(k.amount as DECIMAL(15,2))) as total_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->where('k.type', 'Kas Keluar')
            ->whereBetween('k.date', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $categoryQuery->where('k.warehouse_id', $warehouse_id);
        }

        $expensesByCategory = $categoryQuery
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'total_amount' => floatval($item->total_amount),
                    'count' => intval($item->count)
                ];
            })
            ->toArray();

        return [
            'total_operating_expenses' => $totalExpenses,
            'expenses_by_category' => $expensesByCategory,
            'expense_details' => [] // Don't return full details to save memory
        ];
    }

    private function calculateOtherIncomeOptimized($fromDate, $endDate, $warehouse_id, $user_id)
    {
        // Get total income using raw SQL
        $totalQuery = DB::table('kas')
            ->select(DB::raw('SUM(CAST(amount as DECIMAL(15,2))) as total_income'))
            ->where('type', 'Kas Masuk')
            ->whereBetween('date', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $totalQuery->where('warehouse_id', $warehouse_id);
        }

        $totalIncome = floatval($totalQuery->first()->total_income ?? 0);

        // Get income by category using optimized query
        $categoryQuery = DB::table('kas as k')
            ->leftJoin('kas_income_items as kii', 'k.kas_income_item_id', '=', 'kii.id')
            ->select(
                DB::raw('COALESCE(kii.name, "Lainnya") as category'),
                DB::raw('SUM(CAST(k.amount as DECIMAL(15,2))) as total_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->where('k.type', 'Kas Masuk')
            ->whereBetween('k.date', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $categoryQuery->where('k.warehouse_id', $warehouse_id);
        }

        $incomeByCategory = $categoryQuery
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'total_amount' => floatval($item->total_amount),
                    'count' => intval($item->count)
                ];
            })
            ->toArray();

        return [
            'total_other_income' => $totalIncome,
            'income_by_category' => $incomeByCategory,
            'income_details' => [] // Don't return full details to save memory
        ];
    }

    private function calculateNetSalesForProduct($sale, $sellDetail)
    {
        $originalQuantity = floatval($sellDetail->quantity ?? 0);
        $originalPrice = floatval($sellDetail->price ?? 0);
        $originalDiskon = floatval($sellDetail->diskon ?? 0);

        // Calculate original revenue
        $originalRevenue = ($originalPrice * $originalQuantity) - $originalDiskon;

        // Get total returned quantity for this product from this sale
        $totalReturnedQuantity = 0;
        $totalReturnedRevenue = 0;

        foreach ($sale->sellReturs as $sellRetur) {
            if ($sellRetur->detail) {
                $productReturns = $sellRetur->detail->where('product_id', $sellDetail->product_id);

                foreach ($productReturns as $returnDetail) {
                    $returnedQty = floatval($returnDetail->qty ?? 0);
                    $returnedPrice = floatval($returnDetail->price ?? 0);

                    // Convert returned quantity to same unit as original sale
                    $returnedQuantityConverted = $this->convertBetweenUnits(
                        $returnedQty,
                        $returnDetail->unit_id,
                        $sellDetail->unit_id,
                        $sellDetail->product
                    );

                    $totalReturnedQuantity += $returnedQuantityConverted;
                    $totalReturnedRevenue += ($returnedPrice * $returnedQty);
                }
            }
        }

        // Calculate net values
        $netQuantity = max(0, $originalQuantity - $totalReturnedQuantity);
        $netRevenue = max(0, $originalRevenue - $totalReturnedRevenue);

        return [
            'net_quantity' => $netQuantity,
            'net_revenue' => $netRevenue
        ];
    }

    private function calculateNetCogsForProduct($soldProduct, $product)
    {
        // Convert original sold quantity to eceran
        $originalQuantityEceran = $this->convertQuantityToEceran(
            $soldProduct->qty,
            $soldProduct->unit_type,
            $product
        );

        // Get total returned quantity for this product (in eceran)
        $totalReturnedQuantityEceran = 0;

        // Find returns for this product from the same time period
        $returns = ProductReport::where('product_id', $product->id)
            ->where('for', 'MASUK')
            ->where('type', 'RETUR PENJUALAN')
            ->where('warehouse_id', $soldProduct->warehouse_id)
            ->get();

        foreach ($returns as $returnReport) {
            $returnQuantityEceran = $this->convertQuantityToEceran(
                $returnReport->qty,
                $returnReport->unit_type,
                $product
            );
            $totalReturnedQuantityEceran += $returnQuantityEceran;
        }

        // Calculate net quantity
        $netQuantityEceran = max(0, $originalQuantityEceran - $totalReturnedQuantityEceran);

        return [
            'net_quantity_eceran' => $netQuantityEceran
        ];
    }

    private function convertBetweenUnits($quantity, $fromUnitId, $toUnitId, $product)
    {
        // If same unit, no conversion needed
        if ($fromUnitId == $toUnitId) {
            return floatval($quantity);
        }

        // Convert to eceran first, then to target unit
        $quantityInEceran = $this->convertToEceran($quantity, $fromUnitId, $product);

        // Convert from eceran to target unit
        if ($product->unit_dus == $toUnitId) {
            $conversion = floatval($product->dus_to_eceran ?? 1);
            return $conversion > 0 ? $quantityInEceran / $conversion : 0;
        } elseif ($product->unit_pak == $toUnitId) {
            $conversion = floatval($product->pak_to_eceran ?? 1);
            return $conversion > 0 ? $quantityInEceran / $conversion : 0;
        } else {
            // Target is eceran
            return $quantityInEceran;
        }
    }

    private function isFullyReturned($sell)
    {
        // Get all sell details for this sale
        $sellDetails = $sell->details;

        if ($sellDetails->isEmpty()) {
            return false;
        }

        // Get all return details for this sale
        $returnDetails = collect();
        foreach ($sell->sellReturs as $sellRetur) {
            if ($sellRetur->detail) {
                $returnDetails = $returnDetails->merge($sellRetur->detail);
            }
        }

        if ($returnDetails->isEmpty()) {
            return false;
        }

        // Check if every product in the sale has been fully returned
        foreach ($sellDetails as $sellDetail) {
            $originalQuantity = $this->convertToEceran(
                $sellDetail->quantity,
                $sellDetail->unit_id,
                $sellDetail->product
            );

            // Get total returned quantity for this product
            $totalReturnedQuantity = 0;
            $productReturns = $returnDetails->where('product_id', $sellDetail->product_id);

            foreach ($productReturns as $returnDetail) {
                $returnedQuantity = $this->convertToEceran(
                    $returnDetail->qty,
                    $returnDetail->unit_id,
                    $sellDetail->product
                );
                $totalReturnedQuantity += $returnedQuantity;
            }

            // If any product is not fully returned, the sale is not fully returned
            if ($totalReturnedQuantity < $originalQuantity) {
                return false;
            }
        }

        // All products have been fully returned
        return true;
    }

    private function convertToEceran($quantity, $unitId, $product)
    {
        $safeQuantity = floatval($quantity ?? 0);

        if ($safeQuantity <= 0) {
            return 0;
        }

        // Check which unit this is and convert accordingly
        if ($product->unit_dus == $unitId) {
            $conversion = floatval($product->dus_to_eceran ?? 1);
            return $safeQuantity * ($conversion > 0 ? $conversion : 1);
        } elseif ($product->unit_pak == $unitId) {
            $conversion = floatval($product->pak_to_eceran ?? 1);
            return $safeQuantity * ($conversion > 0 ? $conversion : 1);
        } else {
            // Assume it's eceran/pcs
            return $safeQuantity;
        }
    }

    private function synchronizeProductData(&$salesData, &$cogsData)
    {
        // Get products that exist in either dataset (but only include non-zero products)
        $validProducts = [];

        // Add products from sales data (only those with positive values)
        foreach ($salesData['sales_by_product'] as $productId => $product) {
            if ($product['quantity_sold'] > 0 || $product['total_revenue'] > 0) {
                $validProducts[$productId] = $product['product_name'];
            }
        }

        // Add products from COGS data (only those with positive values)
        foreach ($cogsData['cogs_by_product'] as $productId => $product) {
            if ($product['quantity_sold_eceran'] > 0 || $product['total_cogs'] > 0) {
                $validProducts[$productId] = $product['product_name'];
            }
        }

        // Filter sales data to only include valid products, add missing ones with zero values
        $filteredSalesData = [];
        foreach ($validProducts as $productId => $productName) {
            if (isset($salesData['sales_by_product'][$productId])) {
                $filteredSalesData[$productId] = $salesData['sales_by_product'][$productId];
            } else {
                $filteredSalesData[$productId] = [
                    'product_name' => $productName,
                    'quantity_sold' => 0,
                    'total_revenue' => 0
                ];
            }
        }

        // Filter COGS data to only include valid products, add missing ones with zero values
        $filteredCogsData = [];
        foreach ($validProducts as $productId => $productName) {
            if (isset($cogsData['cogs_by_product'][$productId])) {
                $filteredCogsData[$productId] = $cogsData['cogs_by_product'][$productId];
            } else {
                $filteredCogsData[$productId] = [
                    'product_name' => $productName,
                    'quantity_sold_eceran' => 0,
                    'cost_price' => 0,
                    'total_cogs' => 0
                ];
            }
        }

        // Sort both arrays by product name
        $sortFunction = function ($a, $b) {
            return strcmp($a['product_name'], $b['product_name']);
        };

        $sortedSales = array_values($filteredSalesData);
        usort($sortedSales, $sortFunction);
        $salesData['sales_by_product'] = $sortedSales;

        $sortedCogs = array_values($filteredCogsData);
        usort($sortedCogs, $sortFunction);
        $cogsData['cogs_by_product'] = $sortedCogs;
    }

    private function convertQuantityToEceran($quantity, $unitType, $product)
    {
        // Ensure quantity is numeric
        $safeQuantity = floatval($quantity ?? 0);

        if ($safeQuantity <= 0) {
            return 0;
        }

        switch ($unitType) {
            case 'DUS':
                $conversion = floatval($product->dus_to_eceran ?? 1);
                return $safeQuantity * ($conversion > 0 ? $conversion : 1);
            case 'PAK':
                $conversion = floatval($product->pak_to_eceran ?? 1);
                return $safeQuantity * ($conversion > 0 ? $conversion : 1);
            case 'ECERAN':
            default:
                return $safeQuantity;
        }
    }

    private function convertQuantityToEceranFromData($quantity, $unitType, $productData)
    {
        // Ensure quantity is numeric
        $safeQuantity = floatval($quantity ?? 0);

        if ($safeQuantity <= 0) {
            return 0;
        }

        switch ($unitType) {
            case 'DUS':
                $conversion = floatval($productData->dus_to_eceran ?? 1);
                return $safeQuantity * ($conversion > 0 ? $conversion : 1);
            case 'PAK':
                $conversion = floatval($productData->pak_to_eceran ?? 1);
                return $safeQuantity * ($conversion > 0 ? $conversion : 1);
            case 'ECERAN':
            default:
                return $safeQuantity;
        }
    }
}
