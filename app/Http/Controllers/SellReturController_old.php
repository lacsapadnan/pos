<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Sell;
use App\Models\SellDetail;
use App\Models\SellRetur;
use App\Models\SellReturCart;
use App\Models\SellReturDetail;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SellReturController extends Controller
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
        $userRoles = auth()->user()->getRoleNames();

        if ($userRoles[0] == 'master') {
            $retur = SellRetur::with('sell.customer', 'product', 'warehouse', 'unit', 'user')->orderBy('id', 'asc')->get();
            return response()->json($retur);
        } else {
            $retur = SellRetur::with('sell.customer', 'product', 'warehouse', 'unit', 'user')->where('warehouse_id', auth()->user()->warehouse_id)->orderBy('id', 'asc')->get();
            return response()->json($retur);
        }
    }

    public function  dataDetail($id)
    {
        $returDetail = SellReturDetail::with('sellRetur', 'product', 'unit')->where('sell_retur_id', $id)->get();
        return response()->json($returDetail);
    }

    public function dataSell()
    {
        $userRoles = auth()->user()->getRoleNames();
        if ($userRoles[0] == 'master') {
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
        return view('pages.retur.list-penjualan');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $returCart = SellReturCart::where('user_id', auth()->id())
            ->where('sell_id', $request->sell_id)
            ->get();

        $totalPrice = 0;

        $sellRetur = SellRetur::create([
            'sell_id' => $request->sell_id,
            'warehouse_id' => auth()->user()->warehouse_id,
            'user_id' => auth()->id(),
            'retur_date' => date('Y-m-d'),
        ]);

        foreach ($returCart as $rc) {
            SellReturDetail::create([
                'sell_retur_id' => $sellRetur->id,
                'product_id' => $rc->product_id,
                'unit_id' => $rc->unit_id,
                'qty' => $rc->quantity,
                'price' => $rc->price,
            ]);

            $sell = Sell::where('id', $request->sell_id)
                ->with('customer')
                ->first();
            $sellDetail = SellDetail::where('sell_id', $request->sell_id)
                ->where('product_id', $rc->product_id)
                ->where('unit_id', $rc->unit_id)
                ->with('product')
                ->first();


            // update grand total
            $sell->grand_total = $sell->grand_total - ($rc->quantity * ($sellDetail->price - $sellDetail->diskon));
            $sell->update();

            // update the sell detail
            $sellDetail->quantity -= $rc->quantity;
            $sellDetail->update();

            // total price
            $totalPrice += $rc->quantity * $rc->price;

            $unit = Unit::find($rc->unit_id);

            if ($rc->unit_id == $sellDetail->product->unit_dus) {
                $unitType = 'DUS';
            } elseif ($rc->unit_id == $sellDetail->product->unit_pak) {
                $unitType = 'PAK';
            } elseif ($rc->unit_id == $sellDetail->product->unit_eceran) {
                $unitType = 'ECERAN';
            }

            ProductReport::create([
                'product_id' => $rc->product_id,
                'warehouse_id' => auth()->user()->warehouse_id,
                'user_id' => auth()->id(),
                'customer_id' => $sell->customer_id,
                'unit' => $unit->name,
                'unit_type' => $unitType,
                'qty' => $rc->quantity,
                'price' => $sellDetail->price - $sellDetail->diskon,
                'for' => 'MASUK',
                'type' => 'RETUR PENJUALAN',
                'description' => 'Retur Penjualan ' . $sell->order_number,
            ]);
        }

        // bring back the stock
        foreach ($returCart as $rc) {
            // check the unit_id is unit_dus, unit_pak or unit_pcs in proudct
            $product = Product::where('id', $rc->product_id)->first();
            $inventory = Inventory::where('product_id', $rc->product_id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->first();

            if ($product->unit_dus == $rc->unit_id) {
                $inventory->quantity += $rc->quantity * $product->dus_to_eceran;
            } elseif ($product->unit_pak == $rc->unit_id) {
                $inventory->quantity += $rc->quantity * $product->pak_to_eceran;
            } elseif ($product->unit_pcs == $rc->unit_id) {
                $inventory->quantity += $rc->quantity;
            }

            $inventory->update();
        }

        if ($sell->status == 'lunas') {
            Cashflow::create([
                'user_id' => auth()->id(),
                'warehouse_id' => auth()->user()->warehouse_id,
                'for' => 'Retur penjualan',
                'description' => 'Retur Penjualan ' . $sell->order_number . ' - ' . $sell->customer->name,
                'out' => $totalPrice,
                'in' => 0,
                'payment_method' => null,
            ]);
        }

        // delete the cart
        SellReturCart::where('user_id', auth()->id())->delete();

        return redirect()->route('penjualan-retur.index')->with('success', 'Retur berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sellId = $id;
        $penjualan = Sell::with('customer', 'warehouse', 'details.product', 'details.unit')->findOrFail($id);
        $cart = SellReturCart::with('product', 'unit')
            ->where('user_id', auth()->id())
            ->where('sell_id', $id)
            ->get();
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
        $inputRequests = $request->input_requests;

        // Loop through each input request
        foreach ($inputRequests as $inputRequest) {
            $productId = $inputRequest['product_id'];
            $unitId = $inputRequest['unit_id'];
            $sellId = $inputRequest['sell_id'];

            if (isset($inputRequest['quantity']) && $inputRequest['quantity']) {
                $quantityRetur = $inputRequest['quantity'];

                // Fetch the sell detail
                $sellDetail = SellDetail::where('sell_id', $sellId)
                    ->where('product_id', $productId)
                    ->where('unit_id', $unitId)
                    ->first();

                // Check if sell detail exists
                if ($sellDetail) {
                    $rules = [
                        'quantity' => 'required|numeric|min:1|max:' . $sellDetail->quantity,
                    ];

                    $message = [
                        'quantity.max' => 'Jumlah retur tidak boleh melebihi jumlah penjualan',
                    ];

                    $validator = Validator::make($inputRequest, $rules, $message);

                    if ($validator->fails()) {
                        return response()->json([
                            'errors' => $validator->errors()->all(),
                        ], 422);
                    }

                    $existingCart = SellReturCart::where('user_id', $userId)
                        ->where('sell_id', $sellId)
                        ->where('product_id', $productId)
                        ->where('unit_id', $unitId)
                        ->first();

                    if ($existingCart) {
                        $existingCart->quantity += $quantityRetur;
                        $existingCart->save();
                    } else {
                        SellReturCart::create([
                            'sell_id' => $sellId,
                            'user_id' => $userId,
                            'product_id' => $productId,
                            'unit_id' => $unitId,
                            'quantity' => $quantityRetur,
                            'price' => $inputRequest['price'],
                        ]);
                    }
                } else {
                    return redirect()->back()->with('error', 'Sell detail not found for the specified product and unit');
                }
            }
        }

        return redirect()->back()->with('success', 'Berhasil menambahkan retur ke keranjang');
    }

    public function destroyCart($id)
    {
        $returCart = SellReturCart::find($id);
        $returCart->delete();

        return redirect()->back();
    }

    public function print($id)
    {
        $sellRetur = SellRetur::with('sell.customer', 'sell.details', 'warehouse', 'user')->where('id', $id)->first();
        // dd($sellRetur->toArray());
        $sellReturDetail = SellReturDetail::with('sellRetur.sell.details', 'product', 'unit')->where('sell_retur_id', $id)->get();
        $returNumber = "PJR-" . date('Ymd') . "-" . str_pad(SellRetur::count() + 1, 4, '0', STR_PAD_LEFT);

        $totalQuantity = $sellReturDetail->count();
        $totalPrice = 0;

        foreach ($sellReturDetail as $prd) {
            $totalPrice += $prd->qty * $prd->price;
        }

        $pdf = Pdf::loadView('pages.retur.print-retur', compact('sellRetur', 'sellReturDetail', 'totalQuantity', 'returNumber', 'totalPrice'));
        return response()->stream(function () use ($pdf) {
            echo $pdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Retur-Penjualan-' . $sellRetur->sell->order_number . '.pdf"'
        ]);
    }
}
