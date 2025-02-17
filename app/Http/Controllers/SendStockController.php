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
        $sendStok = SendStock::with('fromWarehouse', 'toWarehouse', 'user')
            ->orderBy('id', 'desc')
            ->get();
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
        $user = auth()->user();
        $fromWarehouse = $user->warehouse_id;
        $toWarehouse = $request->input('to_warehouse');

        // Fetch cart items with products
        $carts = SendStockCart::with('product')->where('user_id', $user->id)->get();

        // Fetch all related inventory records in one query (avoid looping queries)
        $productIds = $carts->pluck('product.id');
        $inventories = Inventory::whereIn('product_id', $productIds)
            ->where('warehouse_id', $fromWarehouse)
            ->get()
            ->keyBy('product_id'); // Store in an associative array for quick lookup

        $stockErrors = [];

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

            // Check available stock
            $fromInventory = $inventories[$product->id] ?? null;

            if (!$fromInventory || $fromInventory->quantity < $quantityEceran) {
                $stockErrors[] = "Stok tidak mencukupi untuk {$product->name}. Dibutuhkan: $quantityEceran, Tersedia: " . ($fromInventory->quantity ?? 0);
            }
        }

        if (!empty($stockErrors)) {
            return redirect()->back()->withErrors($stockErrors);
        }

        $sendStock = SendStock::create([
            'user_id' => $user->id,
            'from_warehouse' => $fromWarehouse,
            'to_warehouse' => $toWarehouse,
        ]);

        $sendStockDetails = [];

        foreach ($carts as $cart) {
            $product = $cart->product;
            $unit = $cart->unit_id;
            $quantity = $cart->quantity;

            $quantityEceran = match ($unit) {
                $product->unit_dus => $quantity * $product->dus_to_eceran,
                $product->unit_pak => $quantity * $product->pak_to_eceran,
                default => $quantity
            };

            // Deduct stock from source warehouse
            Inventory::where('id', $inventories[$product->id]->id)->decrement('quantity', $quantityEceran);

            // Increase stock in destination warehouse (efficient atomic update)
            Inventory::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $toWarehouse],
                ['quantity' => DB::raw("quantity + $quantityEceran")]
            );

            // Prepare batch insert data for SendStockDetail
            $sendStockDetails[] = [
                'send_stock_id' => $sendStock->id,
                'product_id' => $product->id,
                'unit_id' => $unit,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all transfer details in one query (batch insert)
        SendStockDetail::insert($sendStockDetails);

        // Clear cart in one query
        SendStockCart::where('user_id', $user->id)->delete();

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
    public function destroy(Request $request, string $id)
    {
        $sendStock = SendStock::findOrFail($id);

        // Fetch related details from SendStockDetail
        $sendStockDetails = SendStockDetail::where('send_stock_id', $id)->get();

        $stockErrors = [];

        // Loop through the details to reverse the stock movement
        foreach ($sendStockDetails as $detail) {
            $product = $detail->product;
            $unit = $detail->unit_id;
            $quantity = $detail->quantity;

            // Convert quantity to eceran
            $quantityEceran = match ($unit) {
                $product->unit_dus => $quantity * $product->dus_to_eceran,
                $product->unit_pak => $quantity * $product->pak_to_eceran,
                default => $quantity
            };

            // Fetch inventories
            $fromInventory = Inventory::where('product_id', $product->id)
                ->where('warehouse_id', $sendStock->from_warehouse)
                ->first();

            $toInventory = Inventory::where('product_id', $product->id)
                ->where('warehouse_id', $sendStock->to_warehouse)
                ->first();

            // Check if there's enough stock in the destination warehouse to decrement
            if (!$toInventory || $toInventory->quantity < $quantityEceran) {
                $stockErrors[] = "Stok tidak mencukupi untuk mengembalikan {$product->name}. Dibutuhkan: $quantityEceran, Tersedia: " . ($toInventory->quantity ?? 0);
            }

            // Revert stock transfer: Increase in source warehouse, Decrease in destination warehouse
            Inventory::where('id', $fromInventory->id)->increment('quantity', $quantityEceran);

            // Decrease stock in destination warehouse
            Inventory::where('id', $toInventory->id)->decrement('quantity', $quantityEceran);
        }

        if (!empty($stockErrors)) {
            return redirect()->back()->withErrors($stockErrors);
        }

        // Delete the SendStockDetail and SendStock entries
        SendStockDetail::where('send_stock_id', $id)->delete();
        $sendStock->delete();

        return redirect()->route('pindah-stok.index')->with('success', 'Stok berhasil dikembalikan.');
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
