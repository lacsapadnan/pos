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
            $sells = Sell::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'customer')->get();
            return response()->json($sells);
        } else {
            $sells = Sell::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'customer')->where('warehouse_id', auth()->user()->warehouse_id)->get();
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
        $cart = SellCart::with('product', 'unit')->get();
        $subtotal = 0;
        foreach ($cart as $c) {
            $subtotal += $c->price * $c->quantity;
        }
        return view('pages.sell.create', compact('inventories', 'products', 'cart', 'subtotal', 'customers', 'orderNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sellCart = SellCart::where('cashier_id', auth()->id())->get();

        $sell = Sell::create([
            'cashier_id' => auth()->id(),
            'warehouse_id' => auth()->user()->warehouse_id,
            'order_number' => $request->order_number,
            'customer_id' => $request->customer,
            'subtotal' => $request->subtotal,
            'grand_total' => $request->grand_total,
            'pay' => $request->pay,
            'change' => $request->change ?? 0,
            'transaction_date' => Carbon::createFromFormat('d/m/Y', $request->transaction_date)->format('Y-m-d'),
            'payment_method' => $request->payment_method,
            'status' => 'success',
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
        $existingCart = SellCart::where('cashier_id', auth()->id())
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
            $priceDus = $product->price_dus;
            $pricePak = $product->price_pak;
            $priceEceran = $product->price_eceran;

            if ($request->has('diskon_dus') && $request->diskon_dus != null) {
                $discountDus = $request->diskon_dus;
                $priceDus -= $discountDus;
            }

            if ($request->has('diskon_pak') && $request->diskon_pak != null) {
                $discountPak = $request->diskon_pak;
                $pricePak -= $discountPak;
            }

            if ($request->has('diskon_eceran') && $request->diskon_eceran != null) {
                $discountEceran = $request->diskon_eceran;
                $priceEceran -= $discountEceran;
            }

            $unitIdDus = $product->unit_dus;
            $unitIdPak = $product->unit_pak;
            $unitIdEceran = $product->unit_eceran;

            if ($quantityDus > 0) {
                SellCart::create([
                    'cashier_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdDus,
                    'quantity' => $quantityDus,
                    'price' => $priceDus,
                ]);

                $quantityInventoryDus = $quantityDus * $product->dus_to_eceran;
            }

            if ($quantityPak > 0) {
                SellCart::create([
                    'cashier_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdPak,
                    'quantity' => $quantityPak,
                    'price' => $pricePak,
                ]);

                $quantityInventoryPak = $quantityPak * $product->pak_to_eceran;
            }

            if ($quantityEceran > 0) {
                SellCart::create([
                    'cashier_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdEceran,
                    'quantity' => $quantityEceran,
                    'price' => $priceEceran,
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
}
