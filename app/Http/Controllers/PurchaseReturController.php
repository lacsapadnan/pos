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
use App\Models\User;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PurchaseReturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masters = User::role('master')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.PurchaseRetur.index', compact('masters', 'warehouses', 'users'));
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
            $retur = PurchaseRetur::with('purchase.supplier', 'warehouse', 'details', 'user')
                ->orderBy('id', 'desc');
        } else {
            $retur = PurchaseRetur::with('purchase.supplier', 'warehouse', 'details', 'user')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('user_id', auth()->id())
                ->orderBy('id', 'desc');
        }

        if ($warehouse) {
            $retur->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $retur->where('user_id', $user_id);
        }

        if ($fromDate && $toDate) {
            $endDate = Carbon::parse($toDate)->endOfDay();

            $retur->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        $retur = $retur->get();
        return response()->json($retur);
    }

    public function dataByPurchaseId($id)
    {
        $userRoles = auth()->user()->getRoleNames();
        $query = PurchaseRetur::with('purchase.supplier', 'warehouse', 'details', 'user');
        if ($userRoles[0] != 'master') {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }
        $retur = $query
            ->select('purchase_returs.*', 'purchase_returs.remark') // Include the 'remark' column directly
            ->where('purchase_id', $id)
            ->orderBy('purchase_returs.id', 'asc')
            ->get();
        return response()->json($retur);
    }

    public function  dataDetail($id)
    {
        $returDetail = PurchaseReturDetail::with('purchaseRetur', 'product', 'unit')->where('purchase_retur_id', $id)->get();
        return response()->json($returDetail);
    }

    public function dataPurchase(Request $request)
    {
        $role = auth()->user()->getRoleNames();
        $user_id = $request->input('cashier_id');
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
            $purchases = Purchase::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'supplier')
                ->orderBy('id', 'desc');
        } else {
            $purchases = Purchase::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'supplier')
                ->where('warehouse_id', auth()->user()->warehouse_id)
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
        $masters = User::role('master')->get();
        $warehouses = Warehouse::all();
        $role = auth()->user()->getRoleNames();
        if ($role[0] == 'master') {
            $users = User::all();
        } else {
            $users = User::where('warehouse_id', auth()->user()->warehouse_id)->get();
        }
        return view('pages.PurchaseRetur.list-pembelian', compact('masters', 'warehouses', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $returCart = PurchaseReturCart::where('user_id', auth()->id())
            ->where('purchase_id', $request->purchase_id)
            ->get();
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
        }

        PurchaseReturCart::where('user_id', auth()->id())->delete();
        return redirect()->route('pembelian-retur.index')->with('success', 'Retur berhasil disimpan');
    }


    public function konfirmReturnPembelian(Request $request)
    {
        $selectedIds = $request->input('selectedIds');
        $totalPrice = 0;
        foreach ($selectedIds as $selectedId) {
            $purchaseReturDetails = DB::table('purchase_retur_details')->where('purchase_retur_id', $selectedId)->get();
            foreach ($purchaseReturDetails as $rc) {
                $purchase= Purchase::where('id', $request->purchase_id)->first();
                $purchaseDetail = PurchaseDetail::where('purchase_id', $request->purchase_id)
                    ->where('product_id', $rc->product_id)
                    ->where('unit_id', $rc->unit_id)
                    ->with('product')
                    ->first();

                // update the purchase grand total
                $purchase->subtotal -= $rc->qty * $purchaseDetail->price_unit;
                $purchase->grand_total -= $rc->qty * $purchaseDetail->price_unit;
                $purchase->update();

                // update the purchase
                $purchaseDetail->quantity -= $rc->qty;
                $purchaseDetail->total_price -= $rc->qty * $purchaseDetail->price_unit;
                $purchaseDetail->update();

                $totalPrice += $rc->qty * $purchaseDetail->price_unit;
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
                    'qty' => $rc->qty,
                    'price' => $purchaseDetail->price_unit,
                    'for' => 'KELUAR',
                    'type' => 'RETUR PEMBELIAN',
                    'description' => 'Retur Pembelian ' . $purchase->order_number,
                ]);


                $product = Product::where('id', $rc->product_id)->first();
                $inventory = Inventory::where('product_id', $rc->product_id)
                    ->where('warehouse_id', auth()->user()->warehouse_id)
                    ->first();

                if ($product->unit_dus == $rc->unit_id) {
                    $inventory->quantity -= $rc->qty * $product->dus_to_eceran;
                } elseif ($product->unit_pak == $rc->unit_id) {
                    $inventory->quantity -= $rc->qty * $product->pak_to_eceran;
                } elseif ($product->unit_pcs == $rc->unit_id) {
                    $inventory->quantity -= $rc->qty;
                }

                $inventory->update();
            }
            // update remark menjadi verify
            DB::table('purchase_returs')
                ->where('id', $selectedId)
                ->update(['remark' => 'verify']);

            Cashflow::create([
                'warehouse_id' => auth()->user()->warehouse_id,
                'user_id' => auth()->id(),
                'for' => 'Retur pembelian',
                'description' => 'Retur Pembelian ' . $purchase->order_number . ' Supplier ' . $purchase->supplier->name,
                'in' => $totalPrice,
                'out' => 0,
                'payment_method' => null,
            ]);
        }
        return response()->json(['message' => 'Return confirmed successfully']);
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

    public function viewReturnPembelian()
    {
        return view('pages.PurchaseRetur.view_return');
    }
}
