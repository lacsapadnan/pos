<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sell;
use App\Models\SellCart;
use App\Models\SellDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.sell.index');
    }

    public function data()
    {
        $userRoles = auth()->user()->getRoleNames();
        if ($userRoles[0] == 'superadmin') {
            $sells = Sell::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'customer')
                ->orderBy('id', 'desc')
                ->get();
            return response()->json($sells);
        } else {
            $sells = Sell::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'customer')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->orderBy('id', 'desc')
                ->get();
            return response()->json($sells);
        }
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
        $cart = SellCart::with('product', 'unit')->orderBy('id', 'desc')->get();
        $subtotal = 0;
        foreach ($cart as $c) {
            $subtotal += ($c->price * $c->quantity) - $c->diskon;
        }
        return view('pages.sell.create', compact('inventories', 'products', 'cart', 'subtotal', 'customers', 'orderNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $sellCart = SellCart::where('cashier_id', auth()->id())->get();

        $transfer = $request->transfer ?? 0;
        $cash = $request->cash ?? 0;

        $pay = $transfer + $cash;

        if ($pay < $request->grand_total) {
            $status = 'piutang';
        } else {
            $status = 'lunas';
        }

        $sell = Sell::create([
            'cashier_id' => auth()->id(),
            'warehouse_id' => auth()->user()->warehouse_id,
            'order_number' => $request->order_number,
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

        foreach ($sellCart as $sc) {
            SellDetail::create([
                'sell_id' => $sell->id,
                'product_id' => $sc->product_id,
                'unit_id' => $sc->unit_id,
                'quantity' => $sc->quantity,
                'price' => $sc->price,
                'diskon' => $sc->diskon,
            ]);
        }

        // delete all purchase cart
        SellCart::where('cashier_id', auth()->id())->delete();
        return redirect()->route('penjualan.index')->with('success', 'penjualan berhasil ditambahkan');
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
        abort(404);
    }

    public function addCart(Request $request)
    {
        $inputRequests = $request->input('requests');

        if (is_null($inputRequests)) {
            // Log the received data for debugging purposes
            Log::error('Invalid input data: requests key is null or not present.');
            return response()->json(['error' => 'Invalid input data.'], 400);
        }

        if (!is_array($inputRequests)) {
            // Log the received data for debugging purposes
            Log::error('Invalid input data: requests key is not an array.');
            return response()->json(['error' => 'Invalid input data.'], 400);
        }

        try {
            DB::beginTransaction();

            foreach ($inputRequests as $inputRequest) {
                // Ensure $inputRequest is an array before proceeding
                if (!is_array($inputRequest)) {
                    continue;
                }

                $productId = $inputRequest['product_id'];

                $quantityDus = isset($inputRequest['quantity_dus']) ? intval($inputRequest['quantity_dus']) : 0;
                $quantityPak = isset($inputRequest['quantity_pak']) ? intval($inputRequest['quantity_pak']) : 0;
                $quantityEceran = isset($inputRequest['quantity_eceran']) ? intval($inputRequest['quantity_eceran']) : 0;

                if (!isset($inputRequest['unit_dus'])) {
                    Log::error('Invalid input data: unit_dus key is missing for product_id ' . $productId);
                    continue;
                }

                if (!isset($inputRequest['unit_pak'])) {
                    Log::error('Invalid input data: unit_pak key is missing for product_id ' . $productId);
                    continue;
                }

                if (!isset($inputRequest['unit_eceran'])) {
                    Log::error('Invalid input data: unit_eceran key is missing for product_id ' . $productId);
                    continue;
                }

                // Process quantity_dus if it exists
                if ($quantityDus) {
                    $this->processCartItem($productId, $quantityDus, $inputRequest['unit_dus'], $inputRequest['price_dus'], $inputRequest['diskon_dus'] ?? 0);
                }

                // Process quantity_pak if it exists
                if ($quantityPak) {
                    $this->processCartItem($productId, $quantityPak, $inputRequest['unit_pak'], $inputRequest['price_pak'], $inputRequest['diskon_pak'] ?? 0);
                }

                // Process quantity_eceran if it exists
                if ($quantityEceran) {
                    $this->processCartItem($productId, $quantityEceran, $inputRequest['unit_eceran'], $inputRequest['price_eceran'], $inputRequest['diskon_eceran'] ?? 0);
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

    public function destroyCart($id)
    {
        $sellCart = SellCart::find($id);
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
        foreach ($details as $d) {
            $totalQuantity += $d->quantity;
        }
        $pdf = Pdf::loadView('pages.sell.print', compact('sell', 'details', 'totalQuantity'));
        return response()->stream(function () use ($pdf) {
            echo $pdf->output();
        }, 200, [
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
            $sell = Sell::with('warehouse', 'customer')
                ->where('status', 'piutang')
                ->get();
        } else {
            $sell = Sell::with('warehouse', 'customer')
                ->where('status', 'piutang')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->get();
        }

        return response()->json($sell);
    }

    public function payCredit(Request $request)
    {
        $sell = Sell::find($request->sell_id);

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

            return redirect()->back()->with('success', 'Pembayaran piutang berhasil');
        }
    }
}
