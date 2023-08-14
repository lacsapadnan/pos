<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sell;
use App\Models\SellDetail;
use App\Models\SellRetur;
use App\Models\SellReturCart;
use App\Models\SellReturDetail;
use Illuminate\Http\Request;

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

        if ($userRoles[0] == 'superadmin') {
            $retur = SellRetur::with('sell.customer', 'product', 'warehouse', 'unit')->get();
            return response()->json($retur);
        } else {
            $retur = SellRetur::with('sell.customer', 'product', 'warehouse', 'unit')->where('warehouse_id', auth()->user()->warehouse_id)->get();
            return response()->json($retur);
        }
    }

    public function  dataDetail($id)
    {
        $returDetail = SellReturDetail::with('sellRetur', 'product', 'unit')->where('sell_retur_id', $id)->get();
        return response()->json($returDetail);
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
        $returCart = SellReturCart::where('user_id', auth()->id())->get();
        foreach ($returCart as $rc) {
            $sellRetur = SellRetur::create([
                'sell_id' => $request->sell_id,
                'warehouse_id' => auth()->user()->warehouse_id,
                'retur_date' => date('Y-m-d'),
            ]);

            SellReturDetail::create([
                'sell_retur_id' => $sellRetur->id,
                'product_id' => $rc->product_id,
                'unit_id' => $rc->unit_id,
                'qty' => $rc->quantity,
            ]);

            // update the sell
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
            }
        }

        return redirect()->back();
    }


    public function destroyCart($id)
    {
        $returCart = SellReturCart::find($id);
        $returCart->delete();

        return redirect()->back();
    }
}
