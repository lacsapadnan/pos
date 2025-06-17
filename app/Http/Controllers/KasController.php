<?php

namespace App\Http\Controllers;

use App\Http\Requests\KasRequest;
use App\Models\Cashflow;
use App\Models\Kas;
use App\Models\KasExpenseItem;
use App\Models\KasIncomeItem;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoice = 'KAS.' . date('Ymd.') . rand(1000, 9999);
        return view('pages.kas.index', compact('invoice'));
    }

    public function income()
    {
        $kasIncomeItem = KasIncomeItem::orderBy('id', 'ASC')->get();
        return response()->json($kasIncomeItem);
    }

    public function expense()
    {
        $kasExpenseItem = KasExpenseItem::orderBy('id', 'ASC')->get();
        return response()->json($kasExpenseItem);
    }

    public function data(Request $request)
{
    $userRoles = auth()->user()->getRoleNames();
    $query = Kas::with(['kas_income_item', 'kas_expense_item', 'warehouse']);

    if ($userRoles[0] !== 'superadmin') {
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

        if ($request->filled('other')) {
            $type = $request->input('type');
            $itemName = $request->input('other');

            if ($type === 'Kas Masuk') {
                // Check if the item already exists
                $kasIncomeItem = KasIncomeItem::where('id', $itemName)->first();

                if ($kasIncomeItem) {
                    $kasIncomeItemId = $kasIncomeItem->id;
                } else {
                    // Save new KasIncomeItem
                    $kasIncomeItem = new KasIncomeItem();
                    $kasIncomeItem->name = $itemName;
                    $kasIncomeItem->save();
                    $kasIncomeItemId = $kasIncomeItem->id;
                }
            } else if ($type === 'Kas Keluar') {
                // Check if the item already exists
                $kasExpenseItem = KasExpenseItem::where('id', $itemName)->first();

                if ($kasExpenseItem) {
                    $kasExpenseItemId = $kasExpenseItem->id;
                } else {
                    // Save new KasExpenseItem
                    $kasExpenseItem = new KasExpenseItem();
                    $kasExpenseItem->name = $itemName;
                    $kasExpenseItem->save();
                    $kasExpenseItemId = $kasExpenseItem->id;
                }
            }
        }

        $kas = Kas::create([
            'kas_income_item_id' => $kasIncomeItemId,
            'kas_expense_item_id' => $kasExpenseItemId,
            'warehouse_id' => auth()->user()->warehouse_id,
            'date' => $request->date,
            'invoice' => $request->invoice,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        if ($type === 'Kas Masuk') {
            Cashflow::create([
                'warehouse_id' => auth()->user()->warehouse_id,
                'user_id' => auth()->id(),
                'for' => 'Kas Masuk',
                'description' => $request->description,
                'in' => $request->amount,
                'out' => 0,
                'payment_method' => null,
            ]);
        } else {
            Cashflow::create([
                'warehouse_id' => auth()->user()->warehouse_id,
                'user_id' => auth()->id(),
                'for' => 'Kas Keluar',
                'description' => $request->description,
                'in' => 0,
                'out' => $request->amount,
                'payment_method' => null,
            ]);
        }

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
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort(404);
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
