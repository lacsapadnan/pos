<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\SendStock;
use App\Models\SendStockCart;
use App\Models\SendStockDetail;
use App\Models\Unit;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SendStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.sendStok.index');
    }

    public function data()
    {
        $sendStok = SendStock::with('fromWarehouse', 'toWarehouse', 'user')->get();
        return response()->json($sendStok);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        $products = Product::orderBy('id', 'asc')->get();
        $units = Unit::orderBy('id', 'asc')->get();
        $cart = SendStockCart::with('product', 'unit')->where('user_id', auth()->id())->get();
        return view('pages.sendStok.create', compact('warehouses', 'products', 'units', 'cart'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fromWarehouse = auth()->user()->warehouse_id;
        $toWarehouse = $request->input('to_warehouse');

        $sendStock = SendStock::create([
            'user_id' => auth()->id(),
            'from_warehouse' => $fromWarehouse,
            'to_warehouse' => $toWarehouse,
        ]);

        $carts = SendStockCart::with('product')->where('user_id', auth()->id())->get();

        foreach ($carts as $cart) {
            $product = $cart->product;
            $unit = $cart->unit_id;
            $quantity = $cart->quantity;

            // Convert quantity to eceran
            $quantityEceran = match ($unit) {
                $product->unit_dus => $quantity * $product->dus_to_eceran,
                $product->unit_pak => $quantity * $product->pak_to_eceran,
                default => $quantity
            };

            // Check stock before deducting
            $fromInventory = Inventory::where('product_id', $product->id)
                ->where('warehouse_id', $fromWarehouse)
                ->first();

            if (!$fromInventory || $fromInventory->quantity < $quantityEceran) {
                return redirect()->back()
                    ->withErrors("Stok tidak mencukupi untuk {$product->name}. Dibutuhkan: $quantityEceran, Tersedia: " . ($fromInventory->quantity ?? 0));
            }

            // Deduct stock from source warehouse
            $fromInventory->decrement('quantity', $quantityEceran);

            // Increase stock in destination warehouse
            Inventory::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $toWarehouse],
                ['quantity' => DB::raw("quantity + $quantityEceran")]
            );

            // Store transfer details
            SendStockDetail::create([
                'send_stock_id' => $sendStock->id,
                'product_id' => $product->id,
                'unit_id' => $unit,
                'quantity' => $quantity, // Keep as inputted unit
            ]);
        }

        // Clear cart
        SendStockCart::where('user_id', auth()->id())->delete();

        return redirect()->route('pindah-stok.index')->with('success', 'Stok berhasil dipindahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sendStockDetail = SendStockDetail::with('product', 'unit',)->where('send_stock_id', $id)->get();
        return response()->json($sendStockDetail);
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
        $existingCart = SendStockCart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
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

        if ($existingCart) {
            $existingCart->quantity += $totalQuantity;
            $existingCart->save();
        } else {
            $unitIdDus = $product->unit_dus;
            $unitIdPak = $product->unit_pak;
            $unitIdEceran = $product->unit_eceran;

            if ($quantityDus > 0) {
                SendStockCart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdDus,
                    'quantity' => $quantityDus,
                ]);
            }

            if ($quantityPak > 0) {
                SendStockCart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdPak,
                    'quantity' => $quantityPak,
                ]);
            }

            if ($quantityEceran > 0) {
                SendStockCart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'unit_id' => $unitIdEceran,
                    'quantity' => $quantityEceran,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Produk berhasil dimasukan ke keranjang');
    }

    public function destroyCart($id)
    {
        $cart = SendStockCart::find($id);
        $cart->delete();

        return redirect()->back();
    }

    public function print($id)
    {
        $sendStock = SendStock::with('fromWarehouse', 'toWarehouse')->where('id', $id)->first();
        $sendStockDetail = SendStockDetail::with('product', 'unit')->where('send_stock_id', $id)->get();
        $sendStockNumber = "PS-" . date('Ymd') . "-" . str_pad(SendStock::count() + 1, 4, '0', STR_PAD_LEFT);
        $totalQuantity = 0;

        $totalQuantity += $sendStockDetail->count();

        $pdf = Pdf::loadView('pages.sendStok.print', compact('sendStock', 'sendStockDetail', 'totalQuantity', 'sendStockNumber'));
        return response()->stream(function () use ($pdf) {
            echo $pdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Pindah-Stok-' . $sendStock->fromWarehouse->name . '-ke-' . $sendStock->toWarehouse->name . '.pdf"'
        ]);
    }
}
