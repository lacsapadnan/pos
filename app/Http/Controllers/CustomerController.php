<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Imports\CustomerImport;
use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.customer.index');
    }

    public function data()
    {
        $customer = Customer::orderBy('id', 'ASC')->get();
        return response()->json($customer);
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
    public function store(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        return redirect()->back()->with('success', 'Customer ' . $customer->name . ' berhasil ditambahkan.');
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
        $customer = Customer::findOrFail($id);
        return view('pages.customer.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->all());
        return redirect()->route('customer.index')->with('success', 'Customer ' . $customer->name . ' berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->back()->with('success', 'Customer ' . $customer->name . ' berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx',
        ], [
            'file.required' => 'File tidak boleh kosong',
            'file.mimes' => 'File harus berupa CSV atau Excel',
        ]);

        Excel::import(new CustomerImport, $request->file('file'));
        return redirect()
            ->back()
            ->with('success', 'Customer berhasil diimport');
    }

    public function downloadTemplate()
    {
        $template = public_path('assets/template/template_import_customer.xlsx');
        return response()->download($template);
    }
}
