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
        $existingCart = PurchaseCart::where('user_id', auth()->id())->get();
        // check if pay less than grand total status is hutang
        if ($request->pay < $request->grand_total) {
            $status = 'hutang';
        } else {
            $status = 'lunas';
        }


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
            'status' => $status,
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
        $userId = auth()->id();
        $requests = $request->input('requests');

        foreach ($requests as $inputRequest) {
            $productId = $inputRequest['product_id'];

            // Process quantity_dus if it exists
            if (isset($inputRequest['quantity_dus']) && $inputRequest['quantity_dus']) {
                $this->processCartItem($userId, $productId, $inputRequest['quantity_dus'], $inputRequest['unit_dus'], $inputRequest['price_dus'], $inputRequest['discount_fix_dus'] ?? 0, $inputRequest['discount_percent_dus'] ?? 0);
            }

            // Process quantity_pak if it exists
            if (isset($inputRequest['quantity_pak']) && $inputRequest['quantity_pak']) {
                $this->processCartItem($userId, $productId, $inputRequest['quantity_pak'], $inputRequest['unit_pak'], $inputRequest['price_pak'], $inputRequest['discount_fix_pak'] ?? 0, $inputRequest['discount_percent_pak'] ?? 0);
            }

            // Process quantity_eceran if it exists
            if (isset($inputRequest['quantity_eceran']) && $inputRequest['quantity_eceran']) {
                $this->processCartItem($userId, $productId, $inputRequest['quantity_eceran'], $inputRequest['unit_eceran'], $inputRequest['price_eceran'], $inputRequest['discount_fix_eceran'] ?? 0, $inputRequest['discount_percent_eceran'] ?? 0);
            }
        }

        return redirect()->back();
    }

    private function processCartItem($userId, $productId, $quantity, $unitId, $price, $discountFix, $discountPercent)
    {
        $totalPrice = 0;

        // calculated total price if discount_fix or discount_percent exists or both exists
        if ($discountFix && $discountPercent) {
            $totalPrice = ($price * $quantity) - $discountFix - ($price * $quantity * $discountPercent / 100);
        } elseif ($discountFix) {
            $totalPrice = ($price * $quantity) - $discountFix;
        } elseif ($discountPercent) {
            $totalPrice = ($price * $quantity) - ($price * $quantity * $discountPercent / 100);
        } else {
            $totalPrice = $price * $quantity;
        }

        $existingCart = PurchaseCart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->first();

        if ($existingCart) {
            $existingCart->quantity += $quantity;
            $existingCart->update();
        } else {
            PurchaseCart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'unit_id' => $unitId,
                'quantity' => $quantity,
                'discount_fix' => $discountFix,
                'discount_percent' => $discountPercent,
                'price_unit' => $price,
                'total_price' => $totalPrice,
            ]);
        }
    }

    public function destroyCart($id)
    {
        $purchaseCart = PurchaseCart::find($id);
        $purchaseCart->delete();

        return redirect()->back();
    }

    public function debt()
    {
        return view('pages.purchase.debt');
    }

    public function dataDebt()
    {
        $userRoles = auth()->user()->getRoleNames();
        if ($userRoles[0] == 'superadmin') {
            $purchases = Purchase::with('supplier', 'treasury', 'warehouse')
                ->where('status', 'hutang')
                ->get();
        } else {
            $purchases = Purchase::with('supplier', 'treasury', 'warehouse')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('status', 'hutang')
                ->get();
        }

        return response()->json($purchases);
    }

    public function payDebt(Request $request)
    {
        $purchase = Purchase::find($request->purchase_id);

        if ($request->pay > $purchase->grand_total) {
            return redirect()->back()->with('error', 'Pembayaran hutang tidak boleh lebih dari total hutang');
        } elseif ($request->pay < 0) {
            return redirect()->back()->with('error', 'Pembayaran hutang tidak boleh kurang dari 0');
        } elseif ($request->pay == 0) {
            return redirect()->back()->with('error', 'Pembayaran hutang tidak boleh 0');
        } else {
            $purchase->pay += $request->pay;

            if ($purchase->pay == $purchase->grand_total) {
                $purchase->status = 'lunas';
            }

            $purchase->save();

            return redirect()->back()->with('success', 'Pembayaran hutang berhasil');
        }
    }
}
