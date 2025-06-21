<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Sell;
use App\Models\SellDetail;
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
        $query = Sell::with(['details.product', 'customer'])
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

        // Get detailed sales by product
        $salesByProduct = [];
        foreach ($sales as $sale) {
            if (!$sale->details) continue;

            foreach ($sale->details as $detail) {
                if (!$detail->product) continue;

                $productId = $detail->product_id;
                if (!isset($salesByProduct[$productId])) {
                    $salesByProduct[$productId] = [
                        'product_name' => $detail->product->name ?? 'Unknown Product',
                        'quantity_sold' => 0,
                        'total_revenue' => 0
                    ];
                }

                // Safe numeric calculations
                $quantity = floatval($detail->quantity ?? 0);
                $price = floatval($detail->price ?? 0);
                $diskon = floatval($detail->diskon ?? 0);

                $salesByProduct[$productId]['quantity_sold'] += $quantity;
                $salesByProduct[$productId]['total_revenue'] += ($price * $quantity) - $diskon;
            }
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'sales_by_product' => array_values($salesByProduct),
            'sales_details' => $sales
        ];
    }

    private function calculateCostOfGoodsSold($fromDate, $endDate, $warehouse_id, $user_id, $warehouse)
    {
        // Get all sold products from ProductReport
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

            // Determine cost price based on warehouse location with safe numeric conversion
            $costPrice = 0;
            if ($isOutOfTown) {
                $costPrice = floatval($product->lastest_price_eceran_out_of_town ?? $product->lastest_price_eceran ?? 0);
            } else {
                $costPrice = floatval($product->lastest_price_eceran ?? 0);
            }

            // Convert quantity to eceran for cost calculation
            $quantityInEceran = $this->convertQuantityToEceran($quantitySold, $soldProduct->unit_type, $product);

            // Safe multiplication
            $productCogs = floatval($quantityInEceran) * floatval($costPrice);
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

            $cogsByProduct[$productId]['quantity_sold_eceran'] += floatval($quantityInEceran);
            $cogsByProduct[$productId]['total_cogs'] += $productCogs;
        }

        return [
            'total_cogs' => $totalCogs,
            'cogs_by_product' => array_values($cogsByProduct),
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
