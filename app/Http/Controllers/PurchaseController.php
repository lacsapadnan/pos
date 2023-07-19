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
        $userRoles = auth()->user()->getRoleNames();

        if ($userRoles[0] == 'superadmin') {
            $purchases = Purchase::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'treasury', 'supplier', 'warehouse')->get();
            return response()->json($purchases);
        } else {
            $purchases = Purchase::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'treasury', 'supplier', 'warehouse')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->get();
            return response()->json($purchases);
        }
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
        $orderNumber = "PL -" . date('Ymd') . "-" . str_pad(Purchase::count() + 1, 4, '0', STR_PAD_LEFT);
        $cart = PurchaseCart::with('product.unit_dus', 'product.unit_pak', 'product.unit_eceran')->where('user_id', auth()->id())->get();
        $subtotal = 0;
        foreach ($cart as $c) {
            $subtotal += $c->total_price;
        }

        return view('pages.purchase.create', compact('treasuries', 'suppliers', 'inventories', 'products', 'units', 'cart', 'subtotal', 'orderNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
        $existingCart = PurchaseCart::where('user_id', auth()->id())->get();

        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'treasury_id' => $request->treasury_id,
            'warehouse_id' => auth()->user()->warehouse_id,
            'order_number' => $request->order_number,
            'invoice' => $request->invoice,
            'subtotal' => $request->subtotal,
            'grand_total' => $request->grand_total,
            'pay' => $request->pay,
            'reciept_date' => Carbon::createFromFormat('d/m/Y', $request->reciept_date)->format('Y-m-d'),
            'description' => $request->description,
            'tax' => $request->tax,
        ]);

        foreach ($existingCart as $cart) {
            PurchaseDetail::create([
                'purchase_id' => $purchase->id,
                'product_id' => $cart->product_id,
                'unit_id' => $cart->unit_id,
                'quantity' => $cart->quantity,
                'discount_fix' => $cart->discount_fix,
                'discount_percent' => $cart->discount_percent,
                'price_unit' => $cart->price_unit,
                'total_price' => $cart->total_price,
            ]);

            // update product price
            $product = Product::find($cart->product_id);
            if ($cart->unit_id == $product->unit_dus) {
                $product->price_dus = $cart->price_unit;
                $product->lastest_price_eceran = $cart->price_unit / $product->dus_to_eceran;
            } elseif ($cart->unit_id == $product->unit_pak) {
                $product->price_pak = $cart->price_unit;
            } elseif ($cart->unit_id == $product->unit_eceran) {
                $product->price_eceran = $cart->price_unit;
            }
            $product->update();

            // update inventory
            $inventory = Inventory::where('warehouse_id', auth()->user()->warehouse_id)
                ->where('product_id', $cart->product_id)
                ->first();

            // check quantity is dus, pak, or eceran
            $quantity = 0;
            if ($cart->unit_id == $product->unit_dus) {
                $quantity = $cart->quantity * $product->dus_to_eceran;
            } elseif ($cart->unit_id == $product->unit_pak) {
                $quantity = $cart->quantity * $product->pak_to_eceran;
            } elseif ($cart->unit_id == $product->unit_eceran) {
                $quantity = $cart->quantity * 1;
            }

            if ($inventory) {
                $inventory->quantity += $quantity;
                $inventory->update();
            } else {
                Inventory::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'product_id' => $cart->product_id,
                    'quantity' => $quantity,
                ]);
            }
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
            $priceDus = $request->price_dus;
            $quantityDus = $request->quantity_dus;
            $discountFixDus = $request->discount_fix_dus ?? 0;
            $discountPercentDus = $request->discount_percent_dus ?? 0;
            $totalPrice = 0;

            // calculated total price if discount_fix or discount_percent exists or both exists using price unit
            if ($discountFixDus && $discountPercentDus) {
                $totalPrice = ($priceDus * $quantityDus) - $discountFixDus - ($priceDus * $quantityDus * $discountPercentDus / 100);
            } elseif ($discountFixDus) {
                $totalPrice = ($priceDus * $quantityDus) - $discountFixDus;
            } elseif ($discountPercentDus) {
                $totalPrice = ($priceDus * $quantityDus) - ($priceDus * $quantityDus * $discountPercentDus / 100);
            } else {
                $totalPrice = $priceDus * $quantityDus;
            }


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
                    'discount_fix' => $discountFixDus,
                    'discount_percent' => $discountPercentDus,
                    'price_unit' => $priceDus,
                    'total_price' => $totalPrice,
                ]);
            }
        }

        // Save quantity_pak if it exists
        if ($request->has('quantity_pak') && $request->quantity_pak) {
            $pricePak = $request->price_pak;
            $quantityPak = $request->quantity_pak;
            $discountFixPak = $request->discount_fix_pak ?? 0;
            $discountPercentPak = $request->discount_percent_pak ?? 0;
            $totalPrice = 0;

            // calculated total price if discount_fix or discount_percent exists or both exists
            if ($discountFixPak && $discountPercentPak) {
                $totalPrice = ($pricePak * $quantityPak) - $discountFixPak - ($pricePak * $quantityPak * $discountPercentPak / 100);
            } elseif ($discountFixPak) {
                $totalPrice = ($pricePak * $quantityPak) - $discountFixPak;
            } elseif ($discountPercentPak) {
                $totalPrice = ($pricePak * $quantityPak) - ($pricePak * $quantityPak * $discountPercentPak / 100);
            } else {
                $totalPrice = $pricePak * $quantityPak;
            }

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
                    'discount_fix' => $discountFixPak,
                    'discount_percent' => $discountPercentPak,
                    'price_unit' => $pricePak,
                    'total_price' => $totalPrice,
                ]);
            }
        }

        // Save quantity_eceran if it exists
        if ($request->has('quantity_eceran') && $request->quantity_eceran) {
            $priceEceran = $request->price_eceran;
            $quantityEceran = $request->quantity_eceran;
            $discountFixEceran = $request->discount_fix_eceran ?? 0;
            $discountPercentEceran = $request->discount_percent_eceran ?? 0;
            $totalPrice = 0;

            // calculated total price if discount_fix or discount_percent exists or both exists
            if ($discountFixEceran && $discountPercentEceran) {
                $totalPrice = ($priceEceran * $quantityEceran) - $discountFixEceran - ($priceEceran * $quantityEceran * $discountPercentEceran / 100);
            } elseif ($discountFixEceran) {
                $totalPrice = ($priceEceran * $quantityEceran) - $discountFixEceran;
            } elseif ($discountPercentEceran) {
                $totalPrice = ($priceEceran * $quantityEceran) - ($priceEceran * $quantityEceran * $discountPercentEceran / 100);
            } else {
                $totalPrice = $priceEceran * $quantityEceran;
            }
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
                    'discount_fix' => $discountFixEceran,
                    'discount_percent' => $discountPercentEceran,
                    'price_unit' => $priceEceran,
                    'total_price' => $totalPrice,
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
