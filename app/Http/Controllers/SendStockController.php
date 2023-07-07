<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\SendStock;
use App\Models\SendStockDetail;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class SendStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.sendStok.index');
    }

    public function data()
    {
        $sendStok = SendStock::with('fromWarehouse', 'toWarehouse')->get();
        return response()->json($sendStok);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        $products = Product::orderBy('id', 'asc')->get();
        $units = Unit::orderBy('id', 'asc')->get();
        return view('pages.sendStok.create', compact('warehouses', 'products', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $fromWarehouse = $request->input('from_warehouse');
        $toWarehouse = $request->input('to_warehouse');
        $productList = $request->input('product_list');
        foreach ($productList as $productData) {
            $productId = $productData['product_id'];
            $unit = $productData['unit'];
            $quantity = $productData['quantity'];

            $product = Product::find($productId);

            // Check if the product is available in the inventory
            $inventory = Inventory::where('product_id', $productId)
                ->where('warehouse_id', $fromWarehouse)
                ->first();
            if ($inventory) {
                // if product not sync with unit
                if ($unit != $product->unit_dus && $unit != $product->unit_pak && $unit != $product->unit_eceran) {
                    // return error show the unit name is not sync with product name
                    return redirect()->back()->withErrors('Unit tidak sesuai dengan produk: ' . $product->name);
                }

                // check the unit is dus, pak or eceran
                if ($unit == $product->unit_dus) {
                    $inventory->quantity += $product->dus_to_eceran * $quantity;
                } elseif ($unit == $product->unit_pak) {
                    $inventory->quantity += $product->pak_to_eceran - $quantity;
                } elseif ($unit == $product->unit_eceran) {
                    $inventory->quantity += $quantity;
                }
            } else {
                // Product does not exist in the inventory, create a new one
                $inventory = new Inventory();
                $inventory->product_id = $productId;
                $inventory->warehouse_id = $toWarehouse;
                $inventory->quantity = $quantity;
                $inventory->save();
            }
        }

        $sendStock = SendStock::create([
            'from_warehouse' => $fromWarehouse,
            'to_warehouse' => $toWarehouse,
        ]);

        foreach ($productList as $productData) {
            $productId = $productData['product_id'];
            $unit = $productData['unit'];
            $quantity = $productData['quantity'];

            $sendStockDetail = new SendStockDetail();
            $sendStockDetail->send_stock_id = $sendStock->id;
            $sendStockDetail->product_id = $productId;
            $sendStockDetail->unit_id = $unit;
            $sendStockDetail->quantity = $quantity;
            $sendStockDetail->save();
        }

        return redirect()->route('pindah-stok.index')->with('success', 'Stok berhasil dikirim');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sendStockDetail = SendStockDetail::with('product', 'unit',)->where('send_stock_id', $id)->get();
        return response()->json($sendStockDetail);
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
