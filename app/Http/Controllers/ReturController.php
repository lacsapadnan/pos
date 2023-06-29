<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Retur;
use App\Models\ReturCart;
use App\Models\Sell;
use App\Models\SellDetail;
use Illuminate\Http\Request;

class ReturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.retur.index');
    }

    public function data()
    {
        $retur = Retur::with('sell', 'product', 'warehouse', 'unit')->where('warehouse_id', auth()->user()->warehouse_id)->get();
        return response()->json($retur);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.retur.list-penjualan');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $returCart = ReturCart::where('user_id', auth()->id())->get();
        foreach ($returCart as $rc) {
            $retur = Retur::create([
                'sell_id' => $request->sell_id,
                'product_id' => $rc->product_id,
                'unit_id' => $rc->unit_id,
                'warehouse_id' => auth()->user()->warehouse_id,
                'retur_date' => date('Y-m-d'),
                'qty' => $rc->quantity,
            ]);
        }

        // update the sell
        foreach ($returCart as $rc) {
            $sell = SellDetail::where('sell_id', $request->sell_id)->where('product_id', $rc->product_id)->where('unit_id', $rc->unit_id)->first();
            $sell->quantity -= $rc->quantity;
            $sell->update();
        }

        // bring back the stock
        foreach ($returCart as $rc) {
            // check the unit_id is unit_dus, unit_pak or unit_pcs in proudct
            $product = Product::where('id', $rc->product_id)->first();
            $inventory = Inventory::where('product_id', $rc->product_id)->first();

            if ($product->unit_dus == $rc->unit_id) {
                $inventory->quantity += $rc->quantity * $product->dus_to_eceran;
            } elseif ($product->unit_pak == $rc->unit_id) {
                $inventory->quantity += $rc->quantity * $product->pak_to_eceran;
            } elseif ($product->unit_pcs == $rc->unit_id) {
                $inventory->quantity += $rc->quantity;
            }

            $inventory->update();
        }

        // delete the cart
        ReturCart::where('user_id', auth()->id())->delete();

        return redirect()->route('retur.index')->with('success', 'Retur berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sellId = $id;
        $penjualan = Sell::with('customer', 'warehouse', 'details.product', 'details.unit')->findOrFail($id);
        $cart = ReturCart::with('product', 'unit')->where('user_id', auth()->id())->get();
        $subtotal = 0;
        foreach ($cart as $c) {
            $subtotal += $c->price * $c->quantity;
        }
        return view('pages.retur.create', compact('penjualan', 'cart', 'subtotal', 'sellId'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function addCart(Request $request)
    {
        $productId = $request->product_id;
        $unitId = $request->unit_id;
        $userId = auth()->id();

        // Save quantity if it exists
        if ($request->has('quantity') && $request->quantity) {
            $quantityRetur = $request->quantity;
            $existingCart = ReturCart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('unit_id', $unitId)
                ->first();

            if ($existingCart) {
                $existingCart->quantity += $quantityRetur;
                $existingCart->update();
            } else {
                ReturCart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'unit_id' => $unitId,
                    'quantity' => $request->quantity,
                    'price' => $request->price,
                ]);
            }
        }

        return redirect()->back();
    }

    public function destroyCart($id)
    {
        $returCart = ReturCart::find($id);
        $returCart->delete();

        return redirect()->back();
    }
}
