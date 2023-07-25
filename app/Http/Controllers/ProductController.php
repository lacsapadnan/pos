<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Imports\ProductImport;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $unit = Unit::all();
        return view('pages.product.index', compact('unit'));
    }

    public function data()
    {
        $product = Product::with('unit_dus', 'unit_pak', 'unit_eceran')->get();
        return response()->json($product);
    }

    public function dataSearch(Request $request)
    {
        $searchQuery = $request->input('searchQuery');

        $query = Product::with('unit_dus', 'unit_pak', 'unit_eceran');

        if ($searchQuery) {
            $query->where('name', 'LIKE', '%' . $searchQuery . '%');
        } else {
            $query->whereRaw('1 = 0'); // Return no results when no search query is provided
        }

        $product = $query->paginate(10); // Adjust the pagination as per your requirements

        // Prepare the JSON response
        $response = [
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $product->total(),
            'recordsFiltered' => $product->total(),
            'data' => $product->items(),
        ];

        return response()->json($response);
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
    public function store(ProductRequest $request)
    {
        Product::create($request->validated());
        return redirect()->back()->with('success', 'Produk berhasil ditambahkan');
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
        $product = Product::findOrFail($id);
        $unit = Unit::orderBy('id', 'asc')->get();
        return view('pages.product.edit', compact('product', 'unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return redirect()->route('produk.index')->with('success', 'Produk berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->back()->with('success', 'Produk berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx',
        ]);

        Excel::import(new ProductImport, $request->file('file'));
        return redirect()
            ->back()
            ->with('success', 'Produk berhasil diimport');
    }

    public function downloadTemplate()
    {
        $template = public_path('assets\template\template_import_produk.xlsx');
        return response()->download($template);
    }
}
