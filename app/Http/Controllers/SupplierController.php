<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Imports\SupplierImport;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.supplier.index');
    }

    public function data()
    {
        $supplier = Supplier::orderBy('id', 'asc')->get();
        return response()->json($supplier);
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
    public function store(SupplierRequest $request)
    {
        Supplier::create($request->validated());
        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
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
        $supplier = Supplier::findOrFail($id);
        return view('pages.supplier.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());
        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return redirect()->back()->with('success', 'Data supplier berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx',
        ], [
            'file.required' => 'File tidak boleh kosong',
            'file.mimes' => 'File harus berupa CSV atau Excel',
        ]);

        Excel::import(new SupplierImport, $request->file('file'));
        return redirect()->back()->with('success', 'Supplier berhasil diimport');
    }

    public function downloadTemplate()
    {
        $template = public_path('assets/template/template_import_supplier.xlsx');
        return response()->download($template);
    }
}
