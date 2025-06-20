<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Purchase;
use App\Models\PurchaseCart;
use App\Models\PurchaseDetail;
use App\Models\PurchaseRetur;
use App\Models\PurchaseReturDetail;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $isExport = $request->input('export', false);

        // Log filter parameters for debugging
        Log::info('Purchase data request filters:', [
            'user_id' => $user_id,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'warehouse' => $warehouse,
            'export' => $isExport,
        ]);

        $defaultDate = now()->format('Y-m-d');

        if (!$fromDate) {
            $fromDate = $defaultDate;
        }

        if (!$toDate) {
            $toDate = $defaultDate;
        }

        if ($role->first() == 'master') {
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

        // Log the result count
        Log::info('Purchase data result count: ' . $purchases->count());

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
            ->whereHas('product', function ($query) {
                $query->where('isShow', true);
            })
            ->get();
        $products = Product::where('isShow', true)->get();
        $units = Unit::all();
        $today = date('Ymd');
        $year = substr($today, 2, 2);
        $today = substr($today, 2);
        $warehouseId = auth()->user()->warehouse_id;
        $userId = auth()->id();

        $lastOrder = Purchase::where('user_id', $userId)
            ->where('warehouse_id', $warehouseId)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastOrder) {
            // Extract the numerical part of the order number and increment it
            $lastOrderNumberPart = explode('-', $lastOrder->order_number);
            $lastOrderNumber = intval(end($lastOrderNumberPart));
            $newOrderNumber = $lastOrderNumber + 1;
        } else {
            // Reset the order number to 1
            $newOrderNumber = 1;
        }

        // Format the new order number with leading zeros
        $formattedOrderNumber = str_pad($newOrderNumber, 4, '0', STR_PAD_LEFT);

        // Generate the order number string with warehouseId in the middle
        $orderNumber = "PL-" . $today . "-" . $warehouseId . auth()->id() . "-" . $formattedOrderNumber;
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

            $subtotal = (int)str_replace(',', '', $request->subtotal ?? 0);
            $remaint = (int)str_replace(',', '', $request->remaint ?? 0);
            $potongan = (int)str_replace(',', '', $request->potongan ?? 0);
            $grandTotal = (int) str_replace('.', '', $request->grand_total);
            $pay = (int) str_replace('.', '', $request->pay);

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
                'subtotal' => $subtotal,
                'potongan' => $potongan,
                'grand_total' => $grandTotal,
                'pay' => $pay,
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

                // Get the warehouse to check if it's out of town
                $warehouse = auth()->user()->warehouse;

                if ($cart->unit_id == $product->unit_dus) {
                    $product->price_dus = $cart->price_unit;
                    $product->lastest_price_eceran = $cart->price_unit / $product->dus_to_eceran;

                    // Update out of town latest price if warehouse is out of town
                    if ($warehouse && $warehouse->isOutOfTown) {
                        $product->lastest_price_eceran_out_of_town = $product->price_sell_dus_out_of_town / $product->dus_to_eceran;
                    }
                } elseif ($cart->unit_id == $product->unit_pak) {
                    $product->price_pak = $cart->price_unit;
                    $product->lastest_price_eceran = $cart->price_unit / $product->pak_to_eceran;

                    // Update out of town latest price if warehouse is out of town
                    if ($warehouse && $warehouse->isOutOfTown) {
                        $product->lastest_price_eceran_out_of_town = $product->price_sell_pak_out_of_town / $product->pak_to_eceran;
                    }
                } elseif ($cart->unit_id == $product->unit_eceran) {
                    $product->price_eceran = $cart->price_unit;

                    // Update out of town latest price if warehouse is out of town
                    if ($warehouse && $warehouse->isOutOfTown) {
                        $product->lastest_price_eceran_out_of_town = $product->price_sell_eceran_out_of_town;
                    }
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
                    'out' => $request->pay - $remaint,
                    'payment_method' => 'kas besar',
                ]);
            } else {
                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Pembelian',
                    'description' => 'Pembelian ' . $request->order_number . ' Supplier ' . $supplier->name,
                    'in' => 0,
                    'out' => $request->pay - $remaint,
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
        // Optimized query with only necessary relationships
        $purchase = Purchase::with([
            'details.product:id,name,price_sell_dus',
            'details.unit:id,name',
            'supplier:id,name',
            'warehouse:id,name'
        ])->findOrFail($id);

        // Only get suppliers for the dropdown
        $suppliers = Supplier::select('id', 'name')->orderBy('name', 'asc')->get();

        // Get only products and units that are currently used in this purchase
        $usedProductIds = $purchase->details->pluck('product_id')->unique();
        $usedUnitIds = $purchase->details->pluck('unit_id')->unique();

        // Get all products for the select dropdowns (optimize by selecting only needed fields)
        $products = Product::select('id', 'name')->where('isShow', true)->orderBy('name', 'asc')->get();

        // Get all units for the select dropdowns
        $units = Unit::select('id', 'name')->orderBy('name', 'asc')->get();

        // Pre-build options for better performance
        $productOptions = $products->mapWithKeys(function ($product) {
            return [$product->id => $product->name];
        });

        $unitOptions = $units->mapWithKeys(function ($unit) {
            return [$unit->id => $unit->name];
        });

        return view('pages.purchase.edit', compact('purchase', 'suppliers', 'productOptions', 'unitOptions'));
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
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.purchase.debt', compact('warehouses', 'users'));
    }

    public function dataDebt(Request $request)
    {
        $userRoles = auth()->user()->getRoleNames();
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $warehouse = $request->input('warehouse');

        if ($userRoles->first() == 'master') {
            $purchases = Purchase::with('supplier', 'treasury', 'warehouse')
                ->where('status', 'hutang');
        } else {
            $purchases = Purchase::with('supplier', 'treasury', 'warehouse')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('status', 'hutang');
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

    public function payDebtPage(string $id)
    {
        $debt = Purchase::with('supplier', 'treasury', 'warehouse')->find($id);
        $purchaseReturs = PurchaseRetur::with(['details' => function ($query) {
            $query->where('status', '!=', 'clearance')
                ->orWhereNull('status');
        }, 'details.product', 'details.unit'])
            ->whereHas('purchase', function ($query) use ($debt) {
                $query->where('supplier_id', $debt->supplier_id);
            })
            ->whereHas('details', function ($query) {
                $query->where('status', '!=', 'clearance')
                    ->orWhereNull('status');
            })
            ->get();

        return view('pages.purchase.payDebt', compact('debt', 'purchaseReturs'));
    }

    public function payDebt(Request $request)
    {
        // Sanitize the inputs
        $retur = intval(str_replace(',', '', $request->input('retur')));
        $potongan = intval(str_replace(',', '', $request->input('potongan')));
        $bayarHutang = intval(str_replace(',', '', $request->input('bayar_hutang')));
        $selectedReturs = array_map('intval', $request->input('selected_returs', []));

        DB::beginTransaction();
        try {
            // Fetch debt
            $debt = Purchase::with('supplier')->find($request->debt_id);
            if (!$debt) {
                return redirect()->back()->withErrors('Debt not found.');
            }

            // Update grand total and payment
            $debt->grand_total -= $retur;
            $debt->pay += $bayarHutang;

            // Update status to 'lunas' if fully paid
            if ($debt->pay >= $debt->grand_total) {
                $debt->status = 'lunas';
            }

            $debt->save();

            // Update selected return products
            $this->updateSelectedReturs($selectedReturs);

            // Create cash flow records
            $this->createCashflow($debt, $bayarHutang, $potongan, $request->payment_method);

            DB::commit();
            return redirect()->route('hutang')->withSuccess('Berhasil bayar hutang');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors($th->getMessage());
        }
    }

    private function updateSelectedReturs(array $selectedReturs)
    {
        foreach ($selectedReturs as $returProduct) {
            $purchaseReturs = PurchaseReturDetail::find($returProduct);
            if ($purchaseReturs) {
                $purchaseReturs->status = 'clearance';
                $purchaseReturs->save();
            } else {
                return redirect()->back()->withErrors("Retur product with ID {$returProduct} not found.");
            }
        }
    }

    private function createCashflow($debt, $bayarHutang, $potongan, $paymentMethod)
    {
        $commonData = [
            'warehouse_id' => $debt->warehouse_id,
            'user_id' => auth()->id(),
            'for' => 'Bayar hutang',
            'description' => 'Bayar hutang ' . $debt->order_number . ' Supplier ' . $debt->supplier->name,
        ];

        // Create cash flow for payment
        $this->storeCashflow(array_merge($commonData, [
            'in' => $paymentMethod === 'transfer' ? $bayarHutang : 0,
            'out' => $paymentMethod === 'transfer' ? 0 : $bayarHutang,
            'payment_method' => $paymentMethod,
        ]));

        // Create cash flow for potongan if applicable
        if ($potongan > 0) {
            $this->storeCashflow(array_merge($commonData, [
                'in' => $potongan,
                'out' => 0,
                'description' => 'Potongan diskon ' . $commonData['description'],
                'payment_method' => $paymentMethod,
            ]));
        }
    }

    private function storeCashflow(array $data)
    {
        Cashflow::create($data);
    }
}
