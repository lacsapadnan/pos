<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Imports\ProductImport;
use App\Models\Category;
use App\Models\Inventory;
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
        $categories = Category::all();
        return view('pages.product.index', compact('unit', 'categories'));
    }

    public function data()
    {
        $product = Product::with('unit_dus', 'unit_pak', 'unit_eceran')->get();
        return response()->json($product);
    }

    public function dataSearch(Request $request)
    {
        $searchQuery = $request->input('searchQuery');
        $selectedCategory = $request->input('category');

        $query = Product::with('unit_dus', 'unit_pak', 'unit_eceran');

        if ($searchQuery) {
            $query->where(function ($innerQuery) use ($searchQuery) {
                $innerQuery
                    ->where('name', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('barcode_dus', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('barcode_eceran', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('barcode_pak', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('group', 'LIKE', '%' . $searchQuery . '%');
            });
        } elseif ($selectedCategory) {
            $query->where(function ($innerQuery) use ($selectedCategory) {
                $innerQuery->where('group', 'LIKE', '%' . $selectedCategory . '%');
            });
        }

        $recordsFiltered = $query->count();

        // Apply pagination using Laravel's paginate() method
        $pageSize = $request->input('length', 10); // Number of records per page, defaults to 10
        $currentPage = $request->input('start', 0) / $pageSize + 1;
        $product = $query->paginate($pageSize, ['*'], 'page', $currentPage);

        // Prepare the JSON response
        $response = [
            'draw' => $request->input('draw', 1),
            'recordsTotal' => Product::count(), // Total count of all records in the table
            'recordsFiltered' => $recordsFiltered,
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
        $category = Category::where('name', $request->category)->first();
        if (!$category) {
            $category = Category::create([
                'name' => $request->category,
            ]);
        } else {
            $category = Category::where('name', $request->category)->first();
        }
        $data = $request->validated();
        $data['group'] = $category->name;
        $product = Product::create($data);

        Inventory::create([
            'product_id' => $product->id,
            'warehouse_id' => auth()->user()->warehouse_id,
            'quantity' => 0,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Produk berhasil ditambahkan');
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
        $categories = Category::all();
        return view('pages.product.edit', compact('product', 'unit', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $category = Category::where('name', $request->category)->first();
        if (!$category) {
            $category = Category::create([
                'name' => $request->category,
            ]);
        } else {
            $category = Category::where('name', $request->category)->first();
        }
        $product->group = $category->name;
        $product->name = $request->name ?? $product->name;
        $product->barcode_dus = $request->barcode_dus ?? $product->barcode_dus;
        $product->barcode_pak = $request->barcode_pak ?? $product->barcode_pak;
        $product->barcode_eceran = $request->barcode_eceran ?? $product->barcode_eceran;
        $product->unit_dus = $request->unit_dus ?? $product->unit_dus;
        $product->unit_pak = $request->unit_pak ?? $product->unit_pak;
        $product->unit_eceran = $request->unit_eceran ?? $product->unit_eceran;
        $product->price_dus = $request->price_dus ?? $product->price_dus;
        $product->price_pak = $request->price_pak ?? $product->price_pak;
        $product->price_eceran = $request->price_eceran ?? $product->price_eceran;
        $product->sales_price = $request->sales_price ?? $product->sales_price;
        $product->lastest_price_eceran = $request->lastest_price_eceran ?? $product->lastest_price_eceran;
        $product->hadiah = $request->hadiah ?? $product->hadiah;
        $product->price_sell_dus = $request->price_sell_dus ?? $product->price_sell_dus;
        $product->price_sell_pak = $request->price_sell_pak ?? $product->price_sell_pak;
        $product->price_sell_eceran = $request->price_sell_eceran ?? $product->price_sell_eceran;
        $product->dus_to_eceran = $request->dus_to_eceran ?? $product->dus_to_eceran;
        $product->pak_to_eceran = $request->pak_to_eceran ?? $product->pak_to_eceran;
        $product->update();
        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()
            ->back()
            ->with('success', 'Produk berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx',
        ]);

        Excel::import(new ProductImport(), $request->file('file'));
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
