<?php

namespace App\Http\Controllers;

use App\Http\Requests\KasRequest;
use App\Models\Cashflow;
use App\Models\Kas;
use App\Models\KasExpenseItem;
use App\Models\KasIncomeItem;
use App\Models\Warehouse;
use App\Services\CashflowService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KasController extends Controller
{
    protected $cashflowService;

    public function __construct(CashflowService $cashflowService)
    {
        $this->cashflowService = $cashflowService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoice = 'KAS.' . date('Ymd.') . rand(1000, 9999);
        $incomeItems = KasIncomeItem::orderBy('name')->get();
        $expenseItems = KasExpenseItem::orderBy('name')->get();

        // Get warehouses for master
        $warehouses = [];
        if (auth()->user()->hasRole('master')) {
            $warehouses = Warehouse::orderBy('name')->get();
        }

        return view('pages.kas.index', compact('invoice', 'incomeItems', 'expenseItems', 'warehouses'));
    }

    public function income()
    {
        $kasIncomeItem = KasIncomeItem::orderBy('name', 'ASC')->get();
        return response()->json($kasIncomeItem);
    }

    public function expense()
    {
        $kasExpenseItem = KasExpenseItem::orderBy('name', 'ASC')->get();
        return response()->json($kasExpenseItem);
    }

    public function data(Request $request)
    {
        $userRoles = auth()->user()->getRoleNames();
        $query = Kas::with(['kas_income_item', 'kas_expense_item', 'warehouse']);

        if ($userRoles[0] !== 'master') {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }
        $query->orderByDesc('id');
        if ($request->ajax()) {
            return datatables()->of($query)->make(true);
        }

        return response()->json($query->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $kasIncomeItemId = null;
        $kasExpenseItemId = null;
        $type = $request->input('type');
        $warehouseId = auth()->user()->hasRole('master') && $request->filled('warehouse_id')
            ? $request->warehouse_id
            : auth()->user()->warehouse_id;

        if ($type === 'Kas Masuk') {
            $kasIncomeItemId = $request->kas_income_item_id;
        } else if ($type === 'Kas Keluar') {
            $kasExpenseItemId = $request->kas_expense_item_id;
        }

        $kas = Kas::create([
            'kas_income_item_id' => $kasIncomeItemId,
            'kas_expense_item_id' => $kasExpenseItemId,
            'warehouse_id' => $warehouseId,
            'date' => $request->date,
            'invoice' => $request->invoice,
            'type' => $type,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        // Handle cashflow using service
        $this->cashflowService->handleKasTransaction(
            warehouseId: $warehouseId,
            type: $type,
            description: $request->description,
            amount: $request->amount
        );

        return redirect()->back()->with('success', 'Kas ' . $kas->invoice . ' berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kas = Kas::with(['kas_income_item', 'kas_expense_item', 'warehouse'])->findOrFail($id);
        $incomeItems = KasIncomeItem::orderBy('name')->get();
        $expenseItems = KasExpenseItem::orderBy('name')->get();

        // Get warehouses for master
        $warehouses = [];
        if (auth()->user()->hasRole('master')) {
            $warehouses = Warehouse::orderBy('name')->get();
        }

        return response()->json([
            'kas' => $kas,
            'incomeItems' => $incomeItems,
            'expenseItems' => $expenseItems,
            'warehouses' => $warehouses
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kas = Kas::findOrFail($id);

        $kasIncomeItemId = null;
        $kasExpenseItemId = null;
        $type = $request->input('type');
        $warehouseId = auth()->user()->hasRole('master') && $request->filled('warehouse_id')
            ? $request->warehouse_id
            : auth()->user()->warehouse_id;

        if ($type === 'Kas Masuk') {
            $kasIncomeItemId = $request->kas_income_item_id;
        } else if ($type === 'Kas Keluar') {
            $kasExpenseItemId = $request->kas_expense_item_id;
        }

        // Update the kas record
        $kas->update([
            'kas_income_item_id' => $kasIncomeItemId,
            'kas_expense_item_id' => $kasExpenseItemId,
            'warehouse_id' => $warehouseId,
            'date' => $request->date,
            'type' => $type,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        // Update corresponding cashflow record
        $cashflow = Cashflow::where('for', $kas->type === 'Kas Masuk' ? 'Kas Masuk' : 'Kas Keluar')
            ->where('description', $kas->description)
            ->where('warehouse_id', $kas->warehouse_id)
            ->first();

        if ($cashflow) {
            if ($type === 'Kas Masuk') {
                $cashflow->update([
                    'warehouse_id' => $warehouseId,
                    'for' => 'Kas Masuk',
                    'description' => $request->description,
                    'in' => $request->amount,
                    'out' => 0,
                ]);
            } else {
                $cashflow->update([
                    'warehouse_id' => $warehouseId,
                    'for' => 'Kas Keluar',
                    'description' => $request->description,
                    'in' => 0,
                    'out' => $request->amount,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Kas ' . $kas->invoice . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kas = Kas::findOrFail($id);
        $kas->delete();

        return redirect()->back()->with('success', 'Kas ' . $kas->invoice . ' berhasil dihapus.');
    }
}
