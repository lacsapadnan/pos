<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryRequest;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::orderBy('id', 'asc')->get();
        $warehouse = Warehouse::orderBy('id', 'asc')->get();
        $categories = Category::all();
        return view('pages.inventory.index', compact('product', 'warehouse', 'categories'));
    }

    public function data(Request $request)
    {
        $userRoles = auth()->user()->roles->pluck('name');
        $category = $request->input('category');
        $warehouseId = $request->input('warehouse_id');

        if ($userRoles[0] == 'master') {
            $query = Inventory::with('product', 'warehouse');

            if ($category) {
                $query->whereHas('product', function ($query) use ($category) {
                    $query->where('group', 'LIKE', '%' . $category . '%');
                });
            }

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $inventory = $query->get();
            return response()->json($inventory);
        } else {
            $query = Inventory::with('product', 'warehouse')->where('warehouse_id', auth()->user()->warehouse_id);

            if ($category) {
                $query->whereHas('product', function ($query) use ($category) {
                    $query->where('group', 'LIKE', '%' . $category . '%');
                });
            }

            $inventory = $query->get();
            return response()->json($inventory);
        }
    }

    public function dataAll(Request $request)
    {
        $searchQuery = $request->input('searchQuery');

        $query = Inventory::with('product', 'warehouse')
            ->where('warehouse_id', auth()->user()->warehouse_id);

        if ($searchQuery) {
            $query->whereHas('product', function ($query) use ($searchQuery) {
                $query->where('name', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('barcode_dus', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('barcode_pak', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('barcode_eceran', 'LIKE', '%' . $searchQuery . '%');
            });
        } else {
            $query->whereRaw('1 = 0'); // Return no results when no search query is provided
        }

        $recordsFiltered = $query->count();

        // Apply pagination using Laravel's paginate() method
        $pageSize = $request->input('length', 10); // Number of records per page, defaults to 10
        $currentPage = $request->input('start', 0) / $pageSize + 1;
        $inventory = $query->paginate($pageSize, ['*'], 'page', $currentPage);

        // Prepare the JSON response
        $response = [
            'draw' => $request->input('draw', 1),
            'recordsTotal' => Inventory::count(), // Total count of all records in the table
            'recordsFiltered' => $recordsFiltered,
            'data' => $inventory->items(),
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
    public function store(InventoryRequest $request)
    {
        Inventory::create($request->validated());
        return redirect()->route('inventori.index')->with('success', 'Inventory berhasil ditambahkan');
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
        $inventory = Inventory::findOrFail($id);
        return response()->json($inventory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->update($request->all());
        return redirect()->route('inventori.index')->with('success', 'Inventory berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();
        return redirect()->route('inventori.index')->with('success', 'Inventory berhasil dihapus');
    }
}
