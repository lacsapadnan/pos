<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseRetur;
use App\Models\PurchaseReturCart;
use App\Models\PurchaseReturDetail;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        if ($userRoles[0] == 'master') {
            $retur = PurchaseRetur::with('purchase.supplier', 'warehouse', 'details', 'user')->orderBy('id', 'desc')->get();
            return response()->json($retur);
        } else {
            $retur = PurchaseRetur::with('purchase.supplier', 'warehouse', 'details', 'user')->where('warehouse_id', auth()->user()->warehouse_id)->orderBy('id', 'desc')->get();
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
        $returCart = PurchaseReturCart::where('user_id', auth()->id())
            ->where('purchase_id', $request->purchase_id)
            ->get();

        $totalPrice = 0;

        $purchaseRetur = PurchaseRetur::create([
            'purchase_id' => $request->purchase_id,
            'warehouse_id' => auth()->user()->warehouse_id,
            'user_id' => auth()->id(),
            'retur_date' => date('Y-m-d'),
        ]);

        foreach ($returCart as $rc) {
            PurchaseReturDetail::create([
                'purchase_retur_id' => $purchaseRetur->id,
                'product_id' => $rc->product_id,
                'unit_id' => $rc->unit_id,
                'price' => $rc->price,
                'qty' => $rc->quantity,
            ]);

            $purchase = Purchase::where('id', $request->purchase_id)->first();
            $purchaseDetail = PurchaseDetail::where('purchase_id', $request->purchase_id)
                ->where('product_id', $rc->product_id)
                ->where('unit_id', $rc->unit_id)
                ->with('product')
                ->first();

            // update the purchase grand total
            $purchase->subtotal -= $rc->quantity * $purchaseDetail->price_unit;
            $purchase->grand_total -= $rc->quantity * $purchaseDetail->price_unit;
            $purchase->update();

            // update the purchase
            $purchaseDetail->quantity -= $rc->quantity;
            $purchaseDetail->total_price -= $rc->quantity * $purchaseDetail->price_unit;
            $purchaseDetail->update();

            $totalPrice += $rc->quantity * $purchaseDetail->price_unit;

            $unit = Unit::find($rc->unit_id);

            if ($rc->unit_id == $purchaseDetail->product->unit_dus) {
                $unitType = 'DUS';
            } elseif ($rc->unit_id == $purchaseDetail->product->unit_pak) {
                $unitType = 'PAK';
            } elseif ($rc->unit_id == $purchaseDetail->product->unit_eceran) {
                $unitType = 'ECERAN';
            }

            ProductReport::create([
                'product_id' => $rc->product_id,
                'warehouse_id' => auth()->user()->warehouse_id,
                'user_id' => auth()->id(),
                'supplier_id' => $purchase->supplier_id,
                'unit' => $unit->name,
                'unit_type' => $unitType,
                'qty' => $rc->quantity,
                'price' => $purchaseDetail->price_unit,
                'for' => 'KELUAR',
                'type' => 'RETUR PEMBELIAN',
                'description' => 'Retur Pembelian ' . $purchase->order_number,
            ]);
        }

        // bring back the stock
        foreach ($returCart as $rc) {
            $product = Product::where('id', $rc->product_id)->first();
            $inventory = Inventory::where('product_id', $rc->product_id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->first();

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

        Cashflow::create([
            'warehouse_id' => auth()->user()->warehouse_id,
            'user_id' => auth()->id(),
            'for' => 'Retur pembelian',
            'description' => 'Retur Pembelian ' . $purchase->order_number . ' Supplier ' . $purchase->supplier->name,
            'in' => $totalPrice,
            'out' => 0,
            'payment_method' => null,
        ]);

        return redirect()->route('pembelian-retur.index')->with('success', 'Retur berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchaseId = $id;
        $pembelian = Purchase::with('supplier', 'warehouse', 'details', 'details.unit')->findOrFail($id);
        $cart = PurchaseReturCart::with('product', 'unit')
            ->where('purchase_id', $id)
            ->where('user_id', auth()->id())->get();
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
        $userId = auth()->id();
        $inputRequests = $request->input_requests;

        foreach ($inputRequests as $inputRequest) {
            $productId = $inputRequest['product_id'];
            $unitId = $inputRequest['unit_id'];
            $purchaseId = $inputRequest['purchase_id'];

            // Save quantity if it exists
            if (isset($inputRequest['quantity']) && $inputRequest['quantity']) {
                $quantityRetur = $inputRequest['quantity'];

                $purchaseDetail = PurchaseDetail::where('purchase_id', $purchaseId)
                    ->where('product_id', $productId)
                    ->where('unit_id', $unitId)
                    ->first();

                if ($purchaseDetail) {
                    $rules = [
                        'quantity' => 'required|numeric|min:1|max:' . $purchaseDetail->quantity,
                    ];

                    $message = [
                        'quantity.max' => 'Jumlah retur tidak boleh melebihi jumlah pembelian',
                    ];

                    $validator = Validator::make($inputRequest, $rules, $message);

                    if ($validator->fails()) {
                        return response()->json([
                            'errors' => $validator->errors()->all(),
                        ], 422);
                    }

                    $existingCart = PurchaseReturCart::where('user_id', $userId)
                        ->where('purchase_id', $purchaseId)
                        ->where('product_id', $productId)
                        ->where('unit_id', $unitId)
                        ->first();

                    if ($existingCart) {
                        $existingCart->quantity += $quantityRetur;
                        $existingCart->save();
                    } else {
                        PurchaseReturCart::create([
                            'purchase_id' => $purchaseId,
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
        $returCart = PurchaseReturCart::find($id);
        $returCart->delete();

        return redirect()->back();
    }

    public function print($id)
    {
        $purchaseRetur = PurchaseRetur::with('purchase.supplier', 'purchase.details', 'warehouse', 'details', 'user')->findOrFail($id);
        $purchaseReturDetail = PurchaseReturDetail::with('product', 'unit')->where('purchase_retur_id', $id)->get();
        $returNumber = "PBR-" . date('Ymd') . "-" . str_pad(PurchaseRetur::count() + 1, 4, '0', STR_PAD_LEFT);
        $totalQuantity = $purchaseReturDetail->count();
        $totalPrice = 0;

        foreach ($purchaseReturDetail as $prd) {
            $totalPrice += $prd->price * $prd->qty;
        }

        $pdf = Pdf::loadView('pages.PurchaseRetur.print-retur', compact('purchaseRetur', 'purchaseReturDetail', 'totalQuantity', 'returNumber', 'totalPrice'));
        return response()->stream(function () use ($pdf) {
            echo $pdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Retur-Pembelian-' . $purchaseRetur->purchase->order_number . '.pdf"'
        ]);
    }
}
