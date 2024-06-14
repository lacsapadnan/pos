<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Purchase;
use App\Models\PurchaseCart;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masters = User::role('master')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.purchase.index', compact('masters', 'warehouses', 'users'));
    }

    public function data(Request $request)
    {
        $role = auth()->user()->getRoleNames();
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $warehouse = $request->input('warehouse');

        $defaultDate = now()->format('Y-m-d');

        if (!$fromDate) {
            $fromDate = $defaultDate;
        }

        if (!$toDate) {
            $toDate = $defaultDate;
        }

        if ($role[0] == 'master') {
            $purchases = Purchase::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'treasury', 'supplier', 'warehouse', 'user')
                ->orderBy('id', 'desc');
        } else {
            $purchases = Purchase::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'treasury', 'supplier', 'warehouse', 'user')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('user_id', auth()->id())
                ->orderBy('id', 'desc');
        }

        if ($warehouse) {
            $purchases->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $purchases->where('user_id', $user_id);
        }

        if ($fromDate && $toDate) {
            $endDate = Carbon::parse($toDate)->endOfDay();

            $purchases->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        $purchases = $purchases->get();
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
        $today = date('Ymd');
        $warehouseId = auth()->user()->warehouse_id;
        $userId = auth()->id();

        $lastOrder = Purchase::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        if ($lastOrder) {
            // Extract the numerical part of the order number and increment it
            $lastOrderNumber = intval(substr($lastOrder->order_number, strrpos($lastOrder->order_number, '-') + 1));
            $newOrderNumber = $lastOrderNumber + 1;
        } else {
            // Reset the order number to 1
            $newOrderNumber = 1;
        }

        // Format the new order number with leading zeros
        $formattedOrderNumber = str_pad($newOrderNumber, 4, '0', STR_PAD_LEFT);

        // Generate the order number string
        $orderNumber = "PL-" . $today . "-" . $formattedOrderNumber;
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
        try {
            $existingCart = PurchaseCart::where('user_id', auth()->id())->get();
            if ($request->pay < $request->grand_total) {
                $status = 'hutang';
            } else {
                $status = 'lunas';
            }

            $purchase = Purchase::create([
                'user_id' => auth()->id(),
                'supplier_id' => $request->supplier_id,
                'treasury_id' => $request->treasury_id,
                'warehouse_id' => auth()->user()->warehouse_id,
                'order_number' => $request->order_number,
                'invoice' => $request->invoice,
                'subtotal' => $request->subtotal,
                'potongan' => $request->potongan,
                'grand_total' => $request->grand_total,
                'pay' => $request->pay,
                'reciept_date' => Carbon::createFromFormat('d/m/Y', $request->reciept_date)->format('Y-m-d'),
                'description' => $request->description,
                'tax' => $request->tax,
                'status' => $status,
            ]);

            $supplier = Supplier::find($request->supplier_id);

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
                    $product->lastest_price_eceran = $cart->price_unit / $product->pak_to_eceran;
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

                $unit = Unit::find($cart->unit_id);

                if ($cart->unit_id == $product->unit_dus) {
                    $unitType = 'DUS';
                } elseif ($cart->unit_id == $product->unit_pak) {
                    $unitType = 'PAK';
                } elseif ($cart->unit_id == $product->unit_eceran) {
                    $unitType = 'ECERAN';
                }

                ProductReport::create([
                    'product_id' => $cart->product_id,
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'supplier_id' => $request->supplier_id,
                    'unit' => $unit->name,
                    'unit_type' => $unitType,
                    'qty' => $cart->quantity,
                    'price' => $cart->price_unit,
                    'for' => 'MASUK',
                    'type' => 'PEMBELIAN',
                    'description' => 'Pembelian ' . $request->order_number,
                ]);
            }

            // clear the cart
            PurchaseCart::where('user_id', auth()->id())->delete();

            if ($request->payment_method == 2) {
                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Pembelian',
                    'description' => 'Pembelian ' . $request->order_number . ' Supplier ' . $supplier->name,
                    'in' => 0,
                    'out' => $request->pay - $request->remaint,
                    'payment_method' => 'kas besar',
                ]);
            } else {
                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Pembelian',
                    'description' => 'Pembelian ' . $request->order_number . ' Supplier ' . $supplier->name,
                    'in' => 0,
                    'out' => $request->pay - $request->remaint,
                    'payment_method' => 'kas kecil',
                ]);
            }

            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil ditambahkan');
        } catch (\Exception $e) {
            // show the error message
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
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
        $purchases = Purchase::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'treasury', 'supplier', 'warehouse')
            ->orderBy('id', 'desc')
            ->find($id);
        $suppliers = Supplier::orderBy('id', 'asc')->get();
        $products = Product::orderBy('id', 'asc')->get();
        $units = Unit::orderBy('id', 'asc')->get();
        return view('pages.purchase.edit', compact('purchases', 'suppliers', 'products', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $product_id = $request->input('product_id', []);
            $unit_id = $request->input('unit_id', []);
            $qty = $request->input('qty', []);
            $discount_fix = $request->input('discount_fix', []);
            $discount_percent = $request->input('discount_percent', []);
            $price_unit = $request->input('price_unit', []);
            $tax = $request->tax;

            $subtotal = 0;

            foreach ($product_id as $key => $productIdValue) {
                $formattedPriceUnit = $price_unit[$key];
                $numericPriceUnit = str_replace(['Rp. ', '.', ','], '', $formattedPriceUnit);

                if (
                    isset($unit_id[$key]) &&
                    isset($qty[$key]) &&
                    isset($discount_fix[$key]) &&
                    isset($discount_percent[$key])
                ) {
                    $subtotal += ($numericPriceUnit - $discount_fix[$key]) * (1 - $discount_percent[$key] / 100) * $qty[$key];
                } else {
                    Log::error("Invalid data at index {$key} for purchase detail update.");
                    throw new \Exception("Invalid data at index {$key} for purchase detail update.");
                }
            }

            if ($tax == null) {
                $tax = 0;
            }
            $grand_total = $subtotal + ($subtotal * $tax / 100);

            $purchase = Purchase::with('details')->find($id);
            $purchase->invoice = $request->invoice ?? $purchase->invoice;
            $purchase->supplier_id = $request->supplier_id ?? $purchase->supplier_id;
            $purchase->subtotal = $subtotal ?? $purchase->subtotal;
            $purchase->tax = $tax ?? $purchase->tax;
            $purchase->grand_total = $grand_total ?? $purchase->grand_total;
            $purchase->update();

            $purchaseDetail = PurchaseDetail::where('purchase_id', $id)->get();
            foreach ($purchaseDetail as $key => $pd) {
                $formattedPriceUnit = $price_unit[$key];
                $numericPriceUnit = str_replace(['Rp. ', '.', ','], '', $formattedPriceUnit);

                $pd->product_id = $product_id[$key];
                $pd->unit_id = $unit_id[$key];
                $pd->quantity = $qty[$key];
                $pd->discount_fix = $discount_fix[$key];
                $pd->discount_percent = $discount_percent[$key];
                $pd->price_unit = $numericPriceUnit;
                $pd->total_price = ($price_unit[$key] - $discount_fix[$key]) * (1 - $discount_percent[$key] / 100) * $qty[$key];
                $pd->update();
            }

            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil diupdate');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['error' => "Gagal mengupdate pembelian, silahkan cek ulang"]);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $purchase = Purchase::find($id);
        $purchase->delete();

        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus');
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
                'price_unit' => $totalPrice / $quantity,
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
        $purchase = Purchase::with('supplier')
            ->find($request->purchase_id);

        if ($request->pay > $purchase->grand_total + $purchase->pay) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembayaran hutang tidak boleh lebih dari total hutang'
            ]);
        } elseif ($request->pay < 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembayaran hutang tidak boleh kurang dari 0'
            ]);
        } elseif ($request->pay == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembayaran hutang tidak boleh 0'
            ]);
        } else {

            if ($request->potongan) {
                $purchase->potongan += $request->potongan;
                $purchase->grand_total -= $request->potongan;
            }

            $purchase->pay += $request->pay;

            if ($purchase->pay == $purchase->grand_total) {
                $purchase->status = 'lunas';
            }

            $purchase->save();

            if ($request->payment == 'transfer') {
                Cashflow::create([
                    'warehouse_id' => $purchase->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Bayar hutang',
                    'description' => 'Bayar hutang ' . $purchase->order_number . 'Supplier ' . $purchase->supplier->name,
                    'in' => $request->pay,
                    'out' => 0,
                    'payment_method' => 'transfer',
                ]);
                // save to cashflow
                Cashflow::create([
                    'warehouse_id' =>  $purchase->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Bayar hutang',
                    'description' => 'Bayar hutang ' . $purchase->order_number . 'Supplier ' . $purchase->supplier->name,
                    'in' => 0,
                    'out' => $request->pay,
                    'payment_method' => 'transfer',
                ]);
            } else {
                Cashflow::create([
                    'warehouse_id' => $purchase->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Bayar hutang',
                    'description' => 'Bayar hutang ' . $purchase->order_number . 'Supplier ' . $purchase->supplier->name,
                    'in' => 0,
                    'out' => $request->pay,
                    'payment_method' => 'cash',
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran hutang berhasil'
            ]);
        }
    }
}
