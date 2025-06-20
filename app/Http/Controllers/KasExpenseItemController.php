<?php

namespace App\Http\Controllers;

use App\Models\KasExpenseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KasExpenseItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenseItems = KasExpenseItem::all();
        return view('pages.kas.expense-item.index', compact('expenseItems'));
    }

    /**
     * Get data for DataTables.
     */
    public function data(Request $request)
    {
        $query = KasExpenseItem::orderBy('name');

        if ($request->ajax()) {
            return DataTables::of($query)->make(true);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:kas_expense_items,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        KasExpenseItem::create([
            'name' => $request->name,
        ]);

        return redirect()->route('kas-expense-item.index')->with('success', 'Item pengeluaran kas berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:kas_expense_items,name,' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $expenseItem = KasExpenseItem::findOrFail($id);
        $expenseItem->update([
            'name' => $request->name,
        ]);

        return redirect()->route('kas-expense-item.index')->with('success', 'Item pengeluaran kas berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $expenseItem = KasExpenseItem::findOrFail($id);

            // Check if the item is being used in Kas records
            $isUsed = $expenseItem->kas()->exists();

            if ($isUsed) {
                return redirect()->route('kas-expense-item.index')->with('error', 'Item pengeluaran kas tidak dapat dihapus karena sedang digunakan');
            }

            $expenseItem->delete();
            return redirect()->route('kas-expense-item.index')->with('success', 'Item pengeluaran kas berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('kas-expense-item.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
