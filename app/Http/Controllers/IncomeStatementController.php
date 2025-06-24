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

    public function data(Request $request)
    {
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date') ?? now()->format('Y-m-d');
        $toDate = $request->input('to_date') ?? now()->format('Y-m-d');
        $warehouse_id = $request->input('warehouse');

        // Check if user can see all income statement data
        if (!auth()->user()->can('lihat semua laba rugi')) {
            // Restrict to user's own warehouse and user data
            $warehouse_id = auth()->user()->warehouse_id;
            $user_id = auth()->id();
        }

        $endDate = Carbon::parse($toDate)->endOfDay();

        // Get warehouse info to determine if it's out of town
        $warehouse = null;
        if ($warehouse_id) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        // Calculate Sales Revenue
        $salesData = $this->calculateSalesRevenue($fromDate, $endDate, $warehouse_id, $user_id);

        // Calculate Cost of Goods Sold
        $cogsData = $this->calculateCostOfGoodsSold($fromDate, $endDate, $warehouse_id, $user_id, $warehouse);

        // Synchronize product lists and ensure consistent ordering
        $this->synchronizeProductData($salesData, $cogsData);

        // Calculate Operating Expenses (Kas Keluar)
        $operatingExpenses = $this->calculateOperatingExpenses($fromDate, $endDate, $warehouse_id, $user_id);

        // Calculate Other Income (Kas Masuk - excluding sales)
        $otherIncome = $this->calculateOtherIncome($fromDate, $endDate, $warehouse_id, $user_id);

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
            ]
        ];

        return response()->json($response);
    }

    private function calculateSalesRevenue($fromDate, $endDate, $warehouse_id, $user_id)
    {
        $query = Sell::with(['details.product', 'customer', 'sellReturs.detail'])
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'batal')
            ->whereBetween('created_at', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }

        if ($user_id) {
            $query->where('cashier_id', $user_id);
        }

        $sales = $query->get();

        // Calculate total revenue with null safety
        $totalRevenue = 0;
        foreach ($sales as $sale) {
            $grandTotal = $sale->grand_total ?? 0;
            if (is_numeric($grandTotal)) {
                $totalRevenue += floatval($grandTotal);
            }
        }

        $totalTransactions = $sales->count();

        // Get detailed sales by product, accounting for returns
        $salesByProduct = [];
        foreach ($sales as $sale) {
            if (!$sale->details) continue;

            foreach ($sale->details as $detail) {
                if (!$detail->product) continue;

                // Calculate net quantities and revenue after returns
                $netSalesData = $this->calculateNetSalesForProduct($sale, $detail);

                // Skip products that have been fully returned
                if ($netSalesData['net_quantity'] <= 0) {
                    continue;
                }

                $productId = $detail->product_id;
                if (!isset($salesByProduct[$productId])) {
                    $salesByProduct[$productId] = [
                        'product_name' => $detail->product->name ?? 'Unknown Product',
                        'quantity_sold' => 0,
                        'total_revenue' => 0
                    ];
                }

                $salesByProduct[$productId]['quantity_sold'] += $netSalesData['net_quantity'];
                $salesByProduct[$productId]['total_revenue'] += $netSalesData['net_revenue'];
            }
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'sales_by_product' => $salesByProduct,
            'sales_details' => $sales
        ];
    }

    private function calculateCostOfGoodsSold($fromDate, $endDate, $warehouse_id, $user_id, $warehouse)
    {
        // Get all sold products from ProductReport that are not from fully returned sales
        $query = ProductReport::with(['product'])
            ->where('for', 'KELUAR')
            ->where('type', 'PENJUALAN')
            ->whereBetween('created_at', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        $soldProducts = $query->get();

        $totalCogs = 0;
        $cogsByProduct = [];
        $isOutOfTown = $warehouse ? ($warehouse->isOutOfTown ?? false) : false;

        foreach ($soldProducts as $soldProduct) {
            if (!$soldProduct->product) continue;

            $product = $soldProduct->product;
            $quantitySold = floatval($soldProduct->qty ?? 0);

            // Skip if no quantity sold
            if ($quantitySold <= 0) continue;

            // Calculate net quantity after returns for this product
            $netQuantityData = $this->calculateNetCogsForProduct($soldProduct, $product);

            // Skip products that have been fully returned
            if ($netQuantityData['net_quantity_eceran'] <= 0) {
                continue;
            }

            // Determine cost price based on warehouse location with safe numeric conversion
            $costPrice = 0;
            if ($isOutOfTown) {
                $costPrice = floatval($product->lastest_price_eceran_out_of_town ?? $product->lastest_price_eceran ?? 0);
            } else {
                $costPrice = floatval($product->lastest_price_eceran ?? 0);
            }

            // Safe multiplication using net quantity
            $productCogs = floatval($netQuantityData['net_quantity_eceran']) * floatval($costPrice);
            $totalCogs += $productCogs;

            $productId = $product->id;
            if (!isset($cogsByProduct[$productId])) {
                $cogsByProduct[$productId] = [
                    'product_name' => $product->name ?? 'Unknown Product',
                    'quantity_sold_eceran' => 0,
                    'cost_price' => $costPrice,
                    'total_cogs' => 0
                ];
            }

            $cogsByProduct[$productId]['quantity_sold_eceran'] += floatval($netQuantityData['net_quantity_eceran']);
            $cogsByProduct[$productId]['total_cogs'] += $productCogs;
        }

        return [
            'total_cogs' => $totalCogs,
            'cogs_by_product' => $cogsByProduct,
            'is_out_of_town' => $isOutOfTown
        ];
    }

    private function calculateOperatingExpenses($fromDate, $endDate, $warehouse_id, $user_id)
    {
        $query = Kas::with(['kas_expense_item'])
            ->where('type', 'Kas Keluar')
            ->whereBetween('date', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }

        $expenses = $query->get();

        // Calculate total expenses with null safety
        $totalExpenses = 0;
        foreach ($expenses as $expense) {
            $amount = $expense->amount ?? 0;
            if (is_numeric($amount)) {
                $totalExpenses += floatval($amount);
            }
        }

        // Group expenses by category
        $expensesByCategory = [];
        foreach ($expenses as $expense) {
            $category = 'Lainnya';
            if ($expense->kas_expense_item && $expense->kas_expense_item->name) {
                $category = $expense->kas_expense_item->name;
            }

            if (!isset($expensesByCategory[$category])) {
                $expensesByCategory[$category] = [
                    'category' => $category,
                    'total_amount' => 0,
                    'count' => 0
                ];
            }

            $amount = floatval($expense->amount ?? 0);
            $expensesByCategory[$category]['total_amount'] += $amount;
            $expensesByCategory[$category]['count']++;
        }

        return [
            'total_operating_expenses' => $totalExpenses,
            'expenses_by_category' => array_values($expensesByCategory),
            'expense_details' => $expenses
        ];
    }

    private function calculateOtherIncome($fromDate, $endDate, $warehouse_id, $user_id)
    {
        $query = Kas::with(['kas_income_item'])
            ->where('type', 'Kas Masuk')
            ->whereBetween('date', [$fromDate, $endDate]);

        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }

        $income = $query->get();

        // Calculate total income with null safety
        $totalIncome = 0;
        foreach ($income as $incomeItem) {
            $amount = $incomeItem->amount ?? 0;
            if (is_numeric($amount)) {
                $totalIncome += floatval($amount);
            }
        }

        // Group income by category
        $incomeByCategory = [];
        foreach ($income as $incomeItem) {
            $category = 'Lainnya';
            if ($incomeItem->kas_income_item && $incomeItem->kas_income_item->name) {
                $category = $incomeItem->kas_income_item->name;
            }

            if (!isset($incomeByCategory[$category])) {
                $incomeByCategory[$category] = [
                    'category' => $category,
                    'total_amount' => 0,
                    'count' => 0
                ];
            }

            $amount = floatval($incomeItem->amount ?? 0);
            $incomeByCategory[$category]['total_amount'] += $amount;
            $incomeByCategory[$category]['count']++;
        }

        return [
            'total_other_income' => $totalIncome,
            'income_by_category' => array_values($incomeByCategory),
            'income_details' => $income
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
}
