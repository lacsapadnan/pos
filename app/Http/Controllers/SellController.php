<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Sell;
use App\Models\SellCart;
use App\Models\SellCartDraft;
use App\Models\SellDetail;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class SellController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masters = User::role('master')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.sell.index', compact('masters', 'warehouses', 'users'));
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
            $sells = Sell::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'customer', 'cashier')
                ->orderBy('id', 'desc');
        } else {
            $sells = Sell::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'customer', 'cashier')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('cashier_id', auth()->id())
                ->orderBy('id', 'desc');
        }

        if ($warehouse) {
            $sells->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $sells->where('cashier_id', $user_id);
        }

        if ($fromDate && $toDate) {
            $endDate = Carbon::parse($toDate)->endOfDay();

            $sells->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        $sells = $sells->get();
        return response()->json($sells);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $inventories = Inventory::with('product')
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->get();
        $products = Product::all();
        $customers = Customer::all();
        $orderNumber = "PJ -" . date('Ymd') . "-" . str_pad(Sell::count() + 1, 4, '0', STR_PAD_LEFT);
        $cart = SellCart::with('product', 'unit')->orderBy('id', 'desc')
            ->where('cashier_id', auth()->id())
            ->get();
        $subtotal = 0;
        foreach ($cart as $c) {
            $subtotal += ($c->price * $c->quantity) - $c->diskon;
        }
        $masters = User::role('master')->get();
        return view('pages.sell.create', compact('inventories', 'products', 'cart', 'subtotal', 'customers', 'orderNumber', 'masters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sellCart = SellCart::where('cashier_id', auth()->id())->get();

        $transfer = $request->transfer ?? 0;
        $cash = $request->cash ?? 0;

        $pay = $transfer + $cash;

        if ($request->status == 'draft') {
            $status = 'draft';
        } elseif ($pay < $request->grand_total) {
            $status = 'piutang';
        } else {
            $status = 'lunas';
        }

        $lastOrder = Sell::latest()->first();

        if ($lastOrder) {
            // Extract the numerical part of the order number and perform modulo operation
            $lastOrderNumber = (int) preg_replace('/[^0-9]/', '', $lastOrder->order_number);
            $newOrderNumber = ($lastOrderNumber % 9999) + 1;
        } else {
            // If there are no previous orders, set the order number to 1
            $newOrderNumber = 1;
        }

        // Format the new order number and create the order number string
        $orderNumber = "PJ -" . date('Ymd') . "-" . str_pad($newOrderNumber, 4, '0', STR_PAD_LEFT);

        $sell = Sell::create([
            'cashier_id' => auth()->id(),
            'warehouse_id' => auth()->user()->warehouse_id,
            'order_number' => $orderNumber,
            'customer_id' => $request->customer,
            'subtotal' => $request->subtotal,
            'grand_total' => $request->grand_total,
            'cash' => $cash,
            'transfer' => $transfer,
            'pay' => $pay,
            'change' => $request->change ?? 0,
            'transaction_date' => Carbon::createFromFormat('d/m/Y', $request->transaction_date)->format('Y-m-d'),
            'payment_method' => $request->payment_method,
            'status' => $status,
        ]);

        $customer = Customer::find($request->customer);

        if ($request->status == 'draft') {
            foreach ($sellCart as $sc) {
                SellCartDraft::create([
                    'sell_id' => $sell->id,
                    'cashier_id' => auth()->id(),
                    'product_id' => $sc->product_id,
                    'unit_id' => $sc->unit_id,
                    'quantity' => $sc->quantity,
                    'price' => $sc->price,
                    'diskon' => $sc->diskon,
                ]);
            }

            SellCart::where('cashier_id', auth()->id())->delete();
        } else {
            foreach ($sellCart as $sc) {
                SellDetail::create([
                    'sell_id' => $sell->id,
                    'product_id' => $sc->product_id,
                    'unit_id' => $sc->unit_id,
                    'quantity' => $sc->quantity,
                    'price' => $sc->price,
                    'diskon' => $sc->diskon,
                ]);

                $unit = Unit::find($sc->unit_id);
                $product = Product::find($sc->product_id);

                if ($sc->unit_id == $product->unit_dus) {
                    $unitType = 'DUS';
                } elseif ($sc->unit_id == $product->unit_pak) {
                    $unitType = 'PAK';
                } elseif ($sc->unit_id == $product->unit_eceran) {
                    $unitType = 'ECERAN';
                }

                ProductReport::create([
                    'product_id' => $sc->product_id,
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'customer_id' => $request->customer,
                    'unit' => $unit->name,
                    'unit_type' => $unitType,
                    'qty' => $sc->quantity,
                    'price' => $sc->price - $sc->diskon,
                    'for' => 'KELUAR',
                    'type' => 'PENJUALAN',
                    'description' => 'Penjualan ' . $orderNumber,
                ]);
            }

            // delete all purchase cart
            SellCart::where('cashier_id', auth()->id())->delete();

            if ($request->payment_method == 'transfer') {
                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Penjualan',
                    'description' => 'Penjualan ' . $orderNumber . 'Customer ' . $customer->name,
                    'in' => $transfer - $sell->change,
                    'out' => 0,
                    'payment_method' => 'transfer',
                ]);
                // save to cashflow
                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Penjualan',
                    'description' => 'Penjualan ' . $orderNumber . 'Customer ' . $customer->name,
                    'in' => 0,
                    'out' => $transfer - $sell->change,
                    'payment_method' => 'transfer',
                ]);
            } elseif ($request->payment_method == 'cash') {
                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Penjualan',
                    'description' => 'Penjualan ' . $orderNumber . 'Customer ' . $customer->name,
                    'in' => $cash - $sell->change,
                    'out' => 0,
                    'payment_method' => 'cash',
                ]);
            } elseif ($request->payment_method == 'split') {
                // format transfer and cash to currency
                $cashFinal = $cash - $sell->change;
                $transferFormat = number_format($transfer, 0, ',', '.');
                $cashFormat = number_format($cashFinal, 0, ',', '.');

                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Penjualan',
                    'description' => 'Penjualan ' . $orderNumber . ' transfer sebesar ' . $transferFormat . ' dan tunai sebesar ' . $cashFormat . 'Customer ' . $customer->name,
                    'in' => 0,
                    'out' => $transfer,
                    'payment_method' => 'split payment',
                ]);

                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Penjualan',
                    'description' => 'Penjualan ' . $orderNumber . ' transfer sebesar ' . $transferFormat . ' dan tunai sebesar ' . $cashFormat . 'Customer ' . $customer->name,
                    'in' => $transfer,
                    'out' => 0,
                    'payment_method' => 'split payment',
                ]);

                Cashflow::create([
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Penjualan',
                    'description' => 'Penjualan ' . $orderNumber . ' transfer sebesar ' . $transferFormat . ' dan tunai sebesar ' . $cashFormat . 'Customer ' . $customer->name,
                    'in' => $sell->grand_total - $transfer ,
                    'out' => 0,
                    'payment_method' => 'split payment',
                ]);
            }
        }

        if ($request->status != 'draft') {
            $printUrl = route('penjualan.print', $sell->id);
            $script = "<script>window.open('$printUrl', '_blank');</script>";
            return Response::make($script . '<script>window.location.href = "' . route('penjualan.index') . '";</script>');
        } else {
            return redirect()->route('penjualan.index');
        }
    }

    public function checkCustomerStatus(Request $request)
    {
        $customerId = $request->input('customer_id'); // Get the customer ID from the request

        // Check if the customer has 'piutang' status
        $customer = Sell::where('customer_id', $customerId)
            ->where('status', 'piutang')
            ->first();

        if ($customer) {
            return response()->json(['status' => 'piutang']);
        } else {
            // Customer does not have 'piutang' status
            return response()->json(['status' => 'not_piutang']);
        }
    }

    public function validateMasterPassword(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        // check the password request is same with user password
        if (password_verify($request->password, $user->password)) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sellDetail = SellDetail::with('product', 'unit')->where('sell_id', $id)->get();
        return response()->json($sellDetail);
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
        $sell = Sell::with('details')->find($id);
        $sell->delete();

        return redirect()->back()->with('success', 'Data penjualan berhasil dihapus');
    }

    public function addCart(Request $request)
    {
        // $inputRequests = $request->input('requests');
        $requests = $request->input('requests');


        try {
            DB::beginTransaction();

            foreach ($requests as $inputRequest) {
                $productId = $inputRequest['product_id'];

                // Process quantity_dus if it exists
                if (isset($inputRequest['quantity_dus']) && $inputRequest['quantity_dus']) {
                    $this->processCartItem($productId, $inputRequest['quantity_dus'], $inputRequest['unit_dus'], $inputRequest['price_dus'], $inputRequest['diskon_dus'] ?? 0);
                    $this->decreaseInventory($productId, $inputRequest['quantity_dus'], $inputRequest['unit_dus']);
                }

                // Process quantity_pak if it exists
                if (isset($inputRequest['quantity_pak']) && $inputRequest['quantity_pak']) {
                    $this->processCartItem($productId, $inputRequest['quantity_pak'], $inputRequest['unit_pak'], $inputRequest['price_pak'], $inputRequest['diskon_pak'] ?? 0);
                    $this->decreaseInventory($productId, $inputRequest['quantity_pak'], $inputRequest['unit_pak']);
                }

                // Process quantity_eceran if it exists
                if (isset($inputRequest['quantity_eceran']) && $inputRequest['quantity_eceran']) {
                    $this->processCartItem($productId, $inputRequest['quantity_eceran'], $inputRequest['unit_eceran'], $inputRequest['price_eceran'], $inputRequest['diskon_eceran'] ?? 0);
                    $this->decreaseInventory($productId, $inputRequest['quantity_eceran'], $inputRequest['unit_eceran']);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception occurred while processing data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add items to cart.'], 500);
        }

        return response()->json(['success' => 'Items added to cart successfully.'], 200);
    }

    private function processCartItem($productId, $quantity, $unitId, $price, $discount)
    {
        // Calculate the total price based on the unit price and quantity
        $totalPrice = $price * $quantity;

        // Apply the discount to the total price if the discount is provided
        if ($discount > 0) {
            $totalPrice -= $discount;
        }

        $existingCart = SellCart::where('cashier_id', auth()->id())
            ->where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->first();

        if ($existingCart) {
            $existingCart->quantity += $quantity;
            $existingCart->save();
        } else {
            SellCart::create([
                'cashier_id' => auth()->id(),
                'product_id' => $productId,
                'unit_id' => $unitId,
                'quantity' => $quantity,
                'price' => $price,
                'diskon' => $discount,
            ]);
        }
    }

    private function decreaseInventory($productId, $quantity, $unitId)
    {
        $product = Product::find($productId);
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->first();

        if ($unitId == $product->unit_dus) {
            $inventory->quantity -= $quantity * $product->dus_to_eceran;
        } elseif ($unitId == $product->unit_pak) {
            $inventory->quantity -= $quantity * $product->pak_to_eceran;
        } elseif ($unitId == $product->unit_eceran) {
            $inventory->quantity -= $quantity;
        }

        $inventory->save();
    }

    public function destroyCart(Request $request, $id)
    {
        $sellCart = SellCart::where('product_id', $request->product_id)
            ->where('id', $id)
            ->first();

        $sellCart->delete();

        // check unit id is unit_dus, unit_pak, or unit_eceran
        $unitId = $sellCart->unit_id;
        $product = Product::find($sellCart->product_id);
        $inventory = Inventory::where('product_id', $sellCart->product_id)
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->first();

        if ($unitId == $product->unit_dus) {
            $inventory->quantity += $sellCart->quantity * $product->dus_to_eceran;
        } elseif ($unitId == $product->unit_pak) {
            $inventory->quantity += $sellCart->quantity * $product->pak_to_eceran;
        } elseif ($unitId == $product->unit_eceran) {
            $inventory->quantity += $sellCart->quantity;
        }

        $inventory->save();

        return redirect()->back();
    }

    public function print($id)
    {
        $sell = Sell::with('warehouse', 'customer', 'cashier')->find($id);
        $details = SellDetail::with('product', 'unit')->where('sell_id', $id)->get();
        $totalQuantity = 0;
        $totalQuantity += $details->count();
        $pdf = Pdf::loadView('pages.sell.print', compact('sell', 'details', 'totalQuantity'));
        return response()->stream(function () use ($pdf) {
            echo $pdf->output();
        }, 200, [
            'attachment' => false,
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Transaksi-' . $sell->order_number . '.pdf"'
        ]);
    }

    public function credit()
    {
        return view('pages.sell.credit');
    }

    public function dataCredit()
    {
        $userRoles = auth()->user()->getRoleNames();

        if ($userRoles[0] == 'superadmin') {
            $sell = Sell::with('warehouse', 'customer', 'cashier')
                ->where('status', 'piutang')
                ->get();
        } else {
            $sell = Sell::with('warehouse', 'customer', 'cashier')
                ->where('status', 'piutang')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->get();
        }

        return response()->json($sell);
    }

    public function payCredit(Request $request)
    {
        $sell = Sell::with('customer')
            ->find($request->sell_id);

        if ($request->pay > $sell->grand_total) {
            return redirect()->back()->with('error', 'Pembayaran piutang tidak boleh lebih dari total piutang');
        } elseif ($request->pay < 0) {
            return redirect()->back()->with('error', 'Pembayaran piutang tidak boleh kurang dari 0');
        } elseif ($request->pay == 0) {
            return redirect()->back()->with('error', 'Pembayaran piutang tidak boleh 0');
        } else {
            $sell->pay += $request->pay;

            if ($sell->pay == $sell->grand_total) {
                $sell->status = 'lunas';
            }

            $sell->save();

            if ($request->payment == 'transfer') {
                Cashflow::create([
                    'warehouse_id' => $sell->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Bayar piutang',
                    'description' => 'Bayar piutang ' . $sell->order_number . 'Customer ' . $sell->customer->name,
                    'in' => $request->pay,
                    'out' => 0,
                    'payment_method' => 'transfer',
                ]);
                // save to cashflow
                Cashflow::create([
                    'warehouse_id' =>  $sell->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Bayar piutang',
                    'description' => 'Bayar piutang ' . $sell->order_number . 'Customer ' . $sell->customer->name,
                    'in' => 0,
                    'out' => $request->pay,
                    'payment_method' => 'transfer',
                ]);
            } else {
                Cashflow::create([
                    'warehouse_id' => $sell->warehouse_id,
                    'user_id' => auth()->id(),
                    'for' => 'Bayar piutang',
                    'description' => 'Bayar piutang ' . $sell->order_number . 'Customer ' . $sell->customer->name,
                    'in' => $request->pay,
                    'out' => 0,
                    'payment_method' => 'cash',
                ]);
            }

            return redirect()->back()->with('success', 'Pembayaran piutang berhasil');
        }
    }
}
