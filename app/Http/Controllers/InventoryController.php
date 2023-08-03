<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryRequest;
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
        return view('pages.inventory.index', compact('product', 'warehouse'));
    }

    public function data()
    {
        $userRoles = auth()->user()->getRoleNames();

        if ($userRoles[0] == 'superadmin') {
            $inventory = Inventory::with('product', 'warehouse')->get();
            return response()->json($inventory);
        } else {
            $inventory = Inventory::with('product', 'warehouse')->where('warehouse_id', auth()->user()->warehouse_id)->get();
            return response()->json($inventory);
        }
    }

    public function dataAll(Request $request)
    {
        $searchQuery = $request->input('searchQuery');

        $query = Inventory::with('product', 'warehouse')
            ->where('warehouse_id', auth()->user()->warehouse_id);

        if ($searchQuery) {
            // Search hanya by field name doangan
            // $query->whereHas('product', function ($query) use ($searchQuery) {
            //     $query->where('name', 'LIKE', '%' . $searchQuery . '%');
            // });

            $query->where(function ($query) use ($searchQuery) {
                $query->whereHas('product', function ($query) use ($searchQuery) {
                    $query->where('name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('barcode_dus', 'LIKE', '%' . $searchQuery . '%');
                });
            });
        } else {
            $query->whereRaw('1 = 0'); // Return no results when no search query is provided
        }

        $inventory = $query->paginate(10); // Adjust the pagination as per your requirements

        // Prepare the JSON response
        $response = [
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $inventory->total(),
            'recordsFiltered' => $inventory->total(),
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
        abort(404);
    }
}
