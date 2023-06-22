<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseCart;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.purchase.index');
    }

    public function data()
    {
        $purchases = Purchase::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'treasury', 'supplier', 'warehouse')->get();
        return response()->json($purchases);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $treasuries = Treasury::all();
        $suppliers = Supplier::all();
        $inventories = Inventory::with('product')
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->get();
        $products = Product::all();
        $units = Unit::all();
        $cart = PurchaseCart::with('product.unit_dus', 'product.unit_pak', 'product.unit_eceran')->get();
        $subtotal = 0;
        foreach ($cart as $c) {
            $subtotal += $c->price * $c->quantity;
        }

        return view('pages.purchase.create', compact('treasuries', 'suppliers', 'inventories', 'products', 'units', 'cart', 'subtotal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $existingCart = PurchaseCart::where('user_id', auth()->id())->get();

        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'treasury_id' => $request->treasury_id,
            'warehouse_id' => auth()->user()->warehouse_id,
            'invoice' => $request->invoice,
            'subtotal' => $request->subtotal,
            'discount' => $request->discount,
            'grand_total' => $request->grand_total,
            'pay' => $request->pay,
            'due_date' => Carbon::createFromFormat('d/m/Y', $request->due_date)->format('Y-m-d'),
            'reciept_date' => Carbon::createFromFormat('d/m/Y', $request->reciept_date)->format('Y-m-d'),
            'description' => $request->description,
        ]);

        foreach ($existingCart as $cart) {
            PurchaseDetail::create([
                'purchase_id' => $purchase->id,
                'product_id' => $cart->product_id,
                'unit_id' => $cart->unit_id,
                'quantity' => $cart->quantity,
                'price' => $cart->price,
            ]);

            // update product price
            $product = Product::find($cart->product_id);
            if ($cart->unit_id == $product->unit_dus) {
                $product->price_dus = $cart->price;
            } elseif ($cart->unit_id == $product->unit_pak) {
                $product->price_pak = $cart->price;
            } elseif ($cart->unit_id == $product->unit_eceran) {
                $product->price_eceran = $cart->price;
            }
            $product->update();
        }

        // clear the cart
        PurchaseCart::where('user_id', auth()->id())->delete();

        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchaseDetail = PurchaseDetail::with('product', 'unit')->where('purchase_id', $id)->get();
        return response()->json($purchaseDetail);
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
        $productId = $request->product_id;
        $userId = auth()->id();

        // Save quantity_dus if it exists
        if ($request->has('quantity_dus') && $request->quantity_dus) {
            $quantityDus = $request->quantity_dus;
            $existingCart = PurchaseCart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('unit_id', $request->unit_dus)
                ->first();

            if ($existingCart) {
                $existingCart->quantity += $quantityDus;
                $existingCart->update();
            } else {
                PurchaseCart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'unit_id' => $request->unit_dus,
                    'quantity' => $quantityDus,
                    'price' => $request->price_dus,
                ]);
            }
        }

        // Save quantity_pak if it exists
        if ($request->has('quantity_pak') && $request->quantity_pak) {
            $quantityPak = $request->quantity_pak;
            $existingCart = PurchaseCart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('unit_id', $request->unit_pak)
                ->first();

            if ($existingCart) {
                $existingCart->quantity += $quantityPak;
                $existingCart->update();
            } else {
                PurchaseCart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'unit_id' => $request->unit_pak,
                    'quantity' => $quantityPak,
                    'price' => $request->price_pak,
                ]);
            }
        }

        // Save quantity_eceran if it exists
        if ($request->has('quantity_eceran') && $request->quantity_eceran) {
            $quantityEceran = $request->quantity_eceran;
            $existingCart = PurchaseCart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('unit_id', $request->unit_eceran)
                ->first();

            if ($existingCart) {
                $existingCart->quantity += $quantityEceran;
                $existingCart->update();
            } else {
                PurchaseCart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'unit_id' => $request->unit_eceran,
                    'quantity' => $quantityEceran,
                    'price' => $request->price_eceran,
                ]);
            }
        }

        return redirect()->back();
    }

    public function destroyCart($id)
    {
        $purchaseCart = PurchaseCart::find($id);
        $purchaseCart->delete();

        return redirect()->back();
    }
}
