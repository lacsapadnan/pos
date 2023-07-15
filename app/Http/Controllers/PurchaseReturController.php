<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseRetur;
use App\Models\PurchaseReturCart;
use App\Models\PurchaseReturDetail;
use Illuminate\Http\Request;

class PurchaseReturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.PurchaseRetur.index');
    }

    public function data()
    {
        $userRoles = auth()->user()->getRoleNames();

        if ($userRoles[0] == 'superadmin') {
            $retur = PurchaseRetur::with('purchase.supplier', 'warehouse', 'details')->get();
            return response()->json($retur);
        } else {
            $retur = PurchaseRetur::with('purchase.supplier', 'warehouse', 'details')->where('warehouse_id', auth()->user()->warehouse_id)->get();
            return response()->json($retur);
        }
    }

    public function  dataDetail($id)
    {
        $returDetail = PurchaseReturDetail::with('purchaseRetur', 'product', 'unit')->where('purchase_retur_id', $id)->get();
        return response()->json($returDetail);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.PurchaseRetur.list-pembelian');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $returCart = PurchaseReturCart::where('user_id', auth()->id())->get();
        foreach ($returCart as $rc) {
            $purchaseRetur = PurchaseRetur::create([
                'purchase_id' => $request->purchase_id,
                'warehouse_id' => auth()->user()->warehouse_id,
                'retur_date' => date('Y-m-d'),
            ]);

            PurchaseReturDetail::create([
                'purchase_retur_id' => $purchaseRetur->id,
                'product_id' => $rc->product_id,
                'unit_id' => $rc->unit_id,
                'qty' => $rc->quantity,
            ]);

            // update the purchase
            $pruchaseDetail = PurchaseDetail::where('purchase_id', $request->purchase_id)->where('product_id', $rc->product_id)->where('unit_id', $rc->unit_id)->first();
            $pruchaseDetail->quantity -= $rc->quantity;
            $pruchaseDetail->update();
        }

        // bring back the stock
        foreach ($returCart as $rc) {
            // check the unit_id is unit_dus, unit_pak or unit_pcs in proudct
            $product = Product::where('id', $rc->product_id)->first();
            $inventory = Inventory::where('product_id', $rc->product_id)->first();

            if ($product->unit_dus == $rc->unit_id) {
                $inventory->quantity -= $rc->quantity * $product->dus_to_eceran;
            } elseif ($product->unit_pak == $rc->unit_id) {
                $inventory->quantity -= $rc->quantity * $product->pak_to_eceran;
            } elseif ($product->unit_pcs == $rc->unit_id) {
                $inventory->quantity -= $rc->quantity;
            }

            $inventory->update();
        }

        // delete the cart
        PurchaseReturCart::where('user_id', auth()->id())->delete();

        return redirect()->route('pembelian-retur.index')->with('success', 'Retur berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchaseId = $id;
        $pembelian = Purchase::with('supplier', 'warehouse', 'details', 'details.unit')->findOrFail($id);
        $cart = PurchaseReturCart::with('product', 'unit')->where('user_id', auth()->id())->get();
        $subtotal = 0;
        foreach ($cart as $c) {
            $subtotal += $c->price * $c->quantity;
        }
        return view('pages.PurchaseRetur.create', compact('pembelian', 'cart', 'subtotal', 'purchaseId'));
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
        $unitId = $request->unit_id;
        $userId = auth()->id();

        // Save quantity if it exists
        if ($request->has('quantity') && $request->quantity) {
            $quantityRetur = $request->quantity;
            $existingCart = PurchaseReturCart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('unit_id', $unitId)
                ->first();

            if ($existingCart) {
                $existingCart->quantity += $quantityRetur;
                $existingCart->update();
            } else {
                PurchaseReturCart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'unit_id' => $unitId,
                    'quantity' => $request->quantity,
                    'price' => $request->price,
                ]);
            }
        }

        return redirect()->back();
    }

    public function destroyCart($id)
    {
        $returCart = PurchaseReturCart::find($id);
        $returCart->delete();

        return redirect()->back();
    }
}
