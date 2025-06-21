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
use App\Models\User;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class SellReturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masters = User::role('master')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.retur.index', compact('masters', 'warehouses', 'users'));
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
            $retur = SellRetur::with('sell.customer', 'product', 'warehouse', 'unit', 'user')->orderBy('id', 'asc');
        } else {
            $retur = SellRetur::with('sell.customer', 'product', 'warehouse', 'unit', 'user')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('user_id', auth()->id())
                ->orderBy('id', 'asc');
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

        $retur->each(function ($sellRetur) {
            $sellRetur->returNumber = "PJR-" . date('Ymd') . "-" . str_pad($sellRetur->id, 4, '0', STR_PAD_LEFT);
        });

        return response()->json($retur);
    }

    public function dataBySaleId($saleId)
    {
        $userRoles = auth()->user()->getRoleNames();

        $query = SellRetur::with('sell.customer', 'product', 'warehouse', 'unit', 'user');

        if ($userRoles[0] != 'master') {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        $retur = $query
            ->select('sell_returs.*', 'sell_returs.remark') // Include the 'remark' column directly
            ->where('sell_id', $saleId)
            ->orderBy('sell_returs.id', 'asc')
            ->get();

        return response()->json($retur);
    }


    public function  dataDetail($id)
    {
        $returDetail = SellReturDetail::with('sellRetur', 'product', 'unit')->where('sell_retur_id', $id)->get();
        return response()->json($returDetail);
    }

    public function dataSell(Request $request)
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
            $sells = Sell::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'customer')
                ->orderBy('id', 'desc');
        } else {
            $sells = Sell::with('details.product.unit_dus', 'details.product.unit_pak', 'details.product.unit_eceran', 'warehouse', 'customer')
                ->where('warehouse_id', auth()->user()->warehouse_id)
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
        $masters = User::role('master')->get();
        $warehouses = Warehouse::all();
        $role = auth()->user()->getRoleNames();
        if ($role[0] == 'master') {
            $users = User::all();
        } else {
            $users = User::where('warehouse_id', auth()->user()->warehouse_id)->get();
        }
        return view('pages.retur.list-penjualan', compact('masters', 'warehouses', 'users'));
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
            $detailRetur = SellReturDetail::create([
                'sell_retur_id' => $sellRetur->id,
                'product_id' => $rc->product_id,
                'unit_id' => $rc->unit_id,
                'qty' => $rc->quantity,
                'price' => $rc->price,
            ]);

            $sell = Sell::where('id', $request->sell_id)
                ->with('customer')
                ->first();

            // Get product for unit conversion
            $product = Product::find($rc->product_id);

            // Convert return quantity to eceran for processing
            $returnQuantityInEceran = $this->convertToEceran($rc->quantity, $rc->unit_id, $product);

            // Get all sell details for this product to deduct proportionally
            $sellDetails = SellDetail::where('sell_id', $request->sell_id)
                ->where('product_id', $rc->product_id)
                ->with('product')
                ->get();

            $remainingToDeduct = $returnQuantityInEceran;

            foreach ($sellDetails as $sellDetail) {
                if ($remainingToDeduct <= 0) break;

                // Convert sell detail quantity to eceran
                $sellDetailEceran = $this->convertToEceran($sellDetail->quantity, $sellDetail->unit_id, $product);

                if ($sellDetailEceran > 0) {
                    // Calculate how much to deduct from this sell detail
                    $deductEceran = min($remainingToDeduct, $sellDetailEceran);

                    // Convert back to the sell detail's unit
                    $deductInOriginalUnit = $this->convertFromEceran($deductEceran, $sellDetail->unit_id, $product);

                    // Update grand total (proportional to deduction)
                    $pricePerUnit = ($sellDetail->price - $sellDetail->diskon);
                    $sell->grand_total = $sell->grand_total - ($deductInOriginalUnit * $pricePerUnit);

                    // Update the sell detail quantity
                    $sellDetail->quantity -= $deductInOriginalUnit;
                    $sellDetail->update();

                    $remainingToDeduct -= $deductEceran;
                }
            }

            $sell->update();

            // total price
            $totalPrice += $rc->quantity * $rc->price;

            $unit = Unit::find($rc->unit_id);

            if ($rc->unit_id == $product->unit_dus) {
                $unitType = 'DUS';
            } elseif ($rc->unit_id == $product->unit_pak) {
                $unitType = 'PAK';
            } elseif ($rc->unit_id == $product->unit_eceran) {
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
                'price' => $rc->price,
                'for' => 'MASUK',
                'type' => 'RETUR PENJUALAN',
                'description' => 'Retur Penjualan ' . $sell->order_number,
            ]);
        }

        // bring back the stock
        foreach ($returCart as $rc) {
            $product = Product::where('id', $rc->product_id)->first();
            $inventory = Inventory::where('product_id', $rc->product_id)
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->first();

            // Convert returned quantity to eceran and add to inventory
            $quantityInEceran = $this->convertToEceran($rc->quantity, $rc->unit_id, $product);
            $inventory->quantity += $quantityInEceran;
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

        $allReturned = true;

        // Query all SellDetail items for the sell_id
        $sellDetails = SellDetail::where('sell_id', $request->sell_id)->get();

        // Check if all SellDetail quantities are 0
        foreach ($sellDetails as $sellDetail) {
            if ($sellDetail->quantity > 0) {
                $allReturned = false;
                break;
            }
        }

        // Update status to "batal" if all items are returned
        $sell = Sell::where('id', $request->sell_id)->first();
        if ($allReturned) {
            $sell->status = 'batal';
            $sell->update();
        }

        // delete the cart
        SellReturCart::where('user_id', auth()->id())->delete();

        return redirect()->route('penjualan-retur.index')->with('success', 'Retur berhasil disimpan');
    }

    public function konfirmReturn(Request $request)
    {
        $selectedIds = $request->input('selectedIds');
        $totalPrice = 0;
        $sell = Sell::where('id', $request->sell_id)
            ->with('customer')
            ->first();

        foreach ($selectedIds as $selectedId) {
            $sellReturDetails = DB::table('sell_retur_details')->where('sell_retur_id', $selectedId)->get();
            foreach ($sellReturDetails as $rc) {
                $sell = Sell::where('id', $request->sell_id)
                    ->with('customer')
                    ->first();

                // Get product for unit conversion
                $product = Product::find($rc->product_id);

                // Convert return quantity to eceran for processing
                $returnQuantityInEceran = $this->convertToEceran($rc->qty, $rc->unit_id, $product);

                // Get all sell details for this product to deduct proportionally
                $sellDetails = SellDetail::where('sell_id', $request->sell_id)
                    ->where('product_id', $rc->product_id)
                    ->with('product')
                    ->get();

                $remainingToDeduct = $returnQuantityInEceran;

                foreach ($sellDetails as $sellDetail) {
                    if ($remainingToDeduct <= 0) break;

                    // Convert sell detail quantity to eceran
                    $sellDetailEceran = $this->convertToEceran($sellDetail->quantity, $sellDetail->unit_id, $product);

                    if ($sellDetailEceran > 0) {
                        // Calculate how much to deduct from this sell detail
                        $deductEceran = min($remainingToDeduct, $sellDetailEceran);

                        // Convert back to the sell detail's unit
                        $deductInOriginalUnit = $this->convertFromEceran($deductEceran, $sellDetail->unit_id, $product);

                        // Update grand total (proportional to deduction)
                        $pricePerUnit = ($sellDetail->price - $sellDetail->diskon);
                        $sell->grand_total = $sell->grand_total - ($deductInOriginalUnit * $pricePerUnit);

                        // Update the sell detail quantity
                        $sellDetail->quantity -= $deductInOriginalUnit;
                        $sellDetail->update();

                        $remainingToDeduct -= $deductEceran;
                    }
                }

                $sell->update();

                // total price
                $totalPrice += $rc->qty * $rc->price;

                $unit = Unit::find($rc->unit_id);

                if ($rc->unit_id == $product->unit_dus) {
                    $unitType = 'DUS';
                } elseif ($rc->unit_id == $product->unit_pak) {
                    $unitType = 'PAK';
                } elseif ($rc->unit_id == $product->unit_eceran) {
                    $unitType = 'ECERAN';
                }
                ProductReport::create([
                    'product_id' => $rc->product_id,
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'user_id' => auth()->id(),
                    'customer_id' => $sell->customer_id,
                    'unit' => $unit->name,
                    'unit_type' => $unitType,
                    'qty' => $rc->qty,
                    'price' => $rc->price,
                    'for' => 'MASUK',
                    'type' => 'RETUR PENJUALAN',
                    'description' => 'Retur Penjualan ' . $sell->order_number,
                ]);

                $inventory = Inventory::where('product_id', $rc->product_id)
                    ->where('warehouse_id', auth()->user()->warehouse_id)
                    ->first();

                // Convert returned quantity to eceran and add to inventory
                $quantityInEceran = $this->convertToEceran($rc->qty, $rc->unit_id, $product);
                $inventory->quantity += $quantityInEceran;
                $inventory->update();
            }
            // update remkar menjadi verify
            DB::table('sell_returs')
                ->where('id', $selectedId)
                ->update(['remark' => 'verify']);

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
        }
        return response()->json(['message' => 'Return confirmed successfully']);
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

    /**
     * Convert quantity to base unit (eceran)
     */
    private function convertToEceran($quantity, $unitId, $product)
    {
        if ($unitId == $product->unit_dus) {
            return $quantity * $product->dus_to_eceran;
        } elseif ($unitId == $product->unit_pak) {
            return $quantity * $product->pak_to_eceran;
        } elseif ($unitId == $product->unit_eceran) {
            return $quantity;
        }
        return 0;
    }

    /**
     * Convert from base unit (eceran) to specific unit
     */
    private function convertFromEceran($quantityEceran, $unitId, $product)
    {
        if ($unitId == $product->unit_dus && $product->dus_to_eceran > 0) {
            return $quantityEceran / $product->dus_to_eceran;
        } elseif ($unitId == $product->unit_pak && $product->pak_to_eceran > 0) {
            return $quantityEceran / $product->pak_to_eceran;
        } elseif ($unitId == $product->unit_eceran) {
            return $quantityEceran;
        }
        return 0;
    }

    /**
     * Get total remaining quantity in eceran for a product in a specific sell
     */
    private function getTotalRemainingQuantityInEceran($sellId, $productId)
    {
        $sellDetails = SellDetail::where('sell_id', $sellId)
            ->where('product_id', $productId)
            ->with('product')
            ->get();

        $totalRemainingEceran = 0;

        foreach ($sellDetails as $sellDetail) {
            $quantityInEceran = $this->convertToEceran(
                $sellDetail->quantity,
                $sellDetail->unit_id,
                $sellDetail->product
            );
            $totalRemainingEceran += $quantityInEceran;
        }

        return $totalRemainingEceran;
    }

    /**
     * Get total returned quantity in eceran for a product from cart
     */
    private function getTotalReturnedQuantityInEceran($userId, $sellId, $productId)
    {
        $cartItems = SellReturCart::where('user_id', $userId)
            ->where('sell_id', $sellId)
            ->where('product_id', $productId)
            ->with('product')
            ->get();

        $totalReturnedEceran = 0;

        foreach ($cartItems as $cartItem) {
            $quantityInEceran = $this->convertToEceran(
                $cartItem->quantity,
                $cartItem->unit_id,
                $cartItem->product
            );
            $totalReturnedEceran += $quantityInEceran;
        }

        return $totalReturnedEceran;
    }

    /**
     * Get available quantities for return in all units
     */
    public function getAvailableReturnQuantities($sellId, $productId)
    {
        $userId = auth()->id();
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Get total remaining quantity in eceran
        $totalRemainingEceran = $this->getTotalRemainingQuantityInEceran($sellId, $productId);

        // Get total already returned quantity in eceran from cart
        $totalReturnedEceran = $this->getTotalReturnedQuantityInEceran($userId, $sellId, $productId);

        // Calculate available quantity for return in eceran
        $availableForReturnEceran = $totalRemainingEceran - $totalReturnedEceran;

        // Convert to different units
        $availableUnits = [
            'eceran' => [
                'unit_id' => $product->unit_eceran,
                'quantity' => $availableForReturnEceran,
                'unit_name' => $product->unit_eceran ? Unit::find($product->unit_eceran)->name : null
            ]
        ];

        if ($product->pak_to_eceran > 0) {
            $availableUnits['pak'] = [
                'unit_id' => $product->unit_pak,
                'quantity' => $this->convertFromEceran($availableForReturnEceran, $product->unit_pak, $product),
                'unit_name' => $product->unit_pak ? Unit::find($product->unit_pak)->name : null
            ];
        }

        if ($product->dus_to_eceran > 0) {
            $availableUnits['dus'] = [
                'unit_id' => $product->unit_dus,
                'quantity' => $this->convertFromEceran($availableForReturnEceran, $product->unit_dus, $product),
                'unit_name' => $product->unit_dus ? Unit::find($product->unit_dus)->name : null
            ];
        }

        return response()->json([
            'product_id' => $productId,
            'total_remaining_eceran' => $totalRemainingEceran,
            'total_returned_eceran' => $totalReturnedEceran,
            'available_for_return_eceran' => $availableForReturnEceran,
            'available_units' => $availableUnits
        ]);
    }

    public function addCart(Request $request)
    {
        $userId = auth()->id();
        $inputRequests = $request->input_requests;

        foreach ($inputRequests as $inputRequest) {
            $productId = $inputRequest['product_id'];
            $unitId = $inputRequest['unit_id'];
            $sellId = $inputRequest['sell_id'];

            if (isset($inputRequest['quantity']) && $inputRequest['quantity']) {
                $quantityRetur = $inputRequest['quantity'];

                // Get product for unit conversion
                $product = Product::find($productId);
                if (!$product) {
                    return response()->json([
                        'errors' => ['Product not found'],
                    ], 422);
                }

                // Convert the return quantity to eceran for validation
                $returnQuantityInEceran = $this->convertToEceran($quantityRetur, $unitId, $product);

                // Get total remaining quantity in eceran from all sell details
                $totalRemainingEceran = $this->getTotalRemainingQuantityInEceran($sellId, $productId);

                // Get total already returned quantity in eceran from cart
                $totalReturnedEceran = $this->getTotalReturnedQuantityInEceran($userId, $sellId, $productId);

                // Calculate available quantity for return
                $availableForReturnEceran = $totalRemainingEceran - $totalReturnedEceran;

                // Validate that return quantity doesn't exceed available quantity
                if ($returnQuantityInEceran > $availableForReturnEceran) {
                    $availableInRequestedUnit = $this->convertFromEceran($availableForReturnEceran, $unitId, $product);

                    return response()->json([
                        'errors' => [
                            'Jumlah retur (' . $quantityRetur . ') melebihi jumlah yang tersedia untuk dikembalikan (' .
                                number_format($availableInRequestedUnit, 2) . ' dalam unit yang diminta)'
                        ],
                    ], 422);
                }

                // Basic validation
                $rules = [
                    'quantity' => 'required|numeric|min:1',
                ];

                $message = [
                    'quantity.required' => 'Jumlah retur harus diisi',
                    'quantity.numeric' => 'Jumlah retur harus berupa angka',
                    'quantity.min' => 'Jumlah retur minimal 1',
                ];

                $validator = Validator::make(['quantity' => $quantityRetur], $rules, $message);

                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors()->all(),
                    ], 422);
                }

                // Check if cart item with same unit already exists
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
        $sellReturDetail = SellReturDetail::with('sellRetur.sell.details', 'product', 'unit')->where('sell_retur_id', $id)->get();
        $returNumber = "PJR-" . date('Ymd') . "-" . str_pad($sellRetur->id, 4, '0', STR_PAD_LEFT);

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

    public function viewReturnPenjualan()
    {
        return view('pages.retur.view_return');
    }
}
