<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\SendStock;
use App\Models\SendStockCart;
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
        $cart = SendStockCart::with('product', 'unit')->where('user_id', auth()->id())->get();
        return view('pages.sendStok.create', compact('warehouses', 'products', 'units', 'cart'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $fromWarehouse = auth()->user()->warehouse_id;
        $toWarehouse = $request->input('to_warehouse');

        $sendStock = SendStock::create([
            'from_warehouse' => $fromWarehouse,
            'to_warehouse' => $toWarehouse,
        ]);

        $carts = SendStockCart::with('product', 'unit')->where('user_id', auth()->id())->get();
        $productList = [];
        foreach ($carts as $cart) {
            $productId = $cart->product_id;
            $unit = $cart->unit_id;
            $quantity = $cart->quantity;

            $productList[] = [
                'product_id' => $productId,
                'unit' => $unit,
                'quantity' => $quantity,
            ];
        }

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

        SendStockCart::where('user_id', auth()->id())->delete();

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

    public function addCart(Request $request)
    {
        $existingCart = SendStockCart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        $inventory = Inventory::where('product_id', $request->product_id)
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->first();

        $product = Product::find($request->product_id);

        if ($request->has('quantity_dus')) {
            $quantityDus = $request->quantity_dus;
        } else {
            $quantityDus = 0;
        }

        if ($request->has('quantity_pak')) {
            $quantityPak = $request->quantity_pak;
        } else {
            $quantityPak = 0;
        }

        if ($request->has('quantity_eceran')) {
            $quantityEceran = $request->quantity_eceran;
        } else {
            $quantityEceran = 0;
        }

        $totalQuantity = $quantityDus + $quantityPak + $quantityEceran;

        $quantityInventoryDus = 0;
        $quantityInventoryPak = 0;
        $quantityInventoryEceran = 0;

        if ($existingCart) {
            $existingCart->quantity += $totalQuantity;
            $existingCart->save();

            $quantityInventoryDus = $quantityDus * $product->dus_to_eceran;
            $quantityInventoryPak = $quantityPak * $product->pak_to_eceran;
            $quantityInventoryEceran = $quantityEceran;

            $inventory->quantity -= $quantityInventoryDus + $quantityInventoryPak + $quantityInventoryEceran;
            $inventory->save();
        } else {
            $unitIdDus = $product->unit_dus;
            $unitIdPak = $product->unit_pak;
            $unitIdEceran = $product->unit_eceran;

            if ($quantityDus > 0) {
                SendStockCart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdDus,
                    'quantity' => $quantityDus,
                ]);

                $quantityInventoryDus = $quantityDus * $product->dus_to_eceran;
            }

            if ($quantityPak > 0) {
                SendStockCart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdPak,
                    'quantity' => $quantityPak,
                ]);

                $quantityInventoryPak = $quantityPak * $product->pak_to_eceran;
            }

            if ($quantityEceran > 0) {
                SendStockCart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdEceran,
                    'quantity' => $quantityEceran,
                ]);

                $quantityInventoryEceran = $quantityEceran;
            }

            $inventory->quantity -= $quantityInventoryDus + $quantityInventoryPak + $quantityInventoryEceran;
            $inventory->save();
        }

        return redirect()->back();
    }

    public function destroyCart($id)
    {
        $cart = SendStockCart::find($id);
        $product = Product::find($cart->product_id);
        $inventory = Inventory::where('product_id', $cart->product_id)
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->first();

        if ($cart->unit_id == $product->unit_dus) {
            $inventory->quantity += $cart->quantity * $product->dus_to_eceran;
        } elseif ($cart->unit_id == $product->unit_pak) {
            $inventory->quantity += $cart->quantity * $product->pak_to_eceran;
        } elseif ($cart->unit_id == $product->unit_eceran) {
            $inventory->quantity += $cart->quantity;
        }

        $cart->delete();
        $inventory->save();

        return redirect()->back();
    }
}
