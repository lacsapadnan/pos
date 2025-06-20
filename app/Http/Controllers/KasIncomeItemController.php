<?php

namespace App\Http\Controllers;

use App\Models\KasIncomeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KasIncomeItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $incomeItems = KasIncomeItem::all();
        return view('pages.kas.income-item.index', compact('incomeItems'));
    }

    /**
     * Get data for DataTables.
     */
    public function data(Request $request)
    {
        $query = KasIncomeItem::orderBy('name');

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
            'name' => 'required|string|max:255|unique:kas_income_items,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        KasIncomeItem::create([
            'name' => $request->name,
        ]);

        return redirect()->route('kas-income-item.index')->with('success', 'Item pendapatan kas berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:kas_income_items,name,' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $incomeItem = KasIncomeItem::findOrFail($id);
        $incomeItem->update([
            'name' => $request->name,
        ]);

        return redirect()->route('kas-income-item.index')->with('success', 'Item pendapatan kas berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $incomeItem = KasIncomeItem::findOrFail($id);

            // Check if the item is being used in Kas records
            $isUsed = $incomeItem->kas()->exists();

            if ($isUsed) {
                return redirect()->route('kas-income-item.index')->with('error', 'Item pendapatan kas tidak dapat dihapus karena sedang digunakan');
            }

            $incomeItem->delete();
            return redirect()->route('kas-income-item.index')->with('success', 'Item pendapatan kas berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('kas-income-item.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
