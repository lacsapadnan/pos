<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\SendStock;
use App\Models\SendStockCart;
use App\Models\SendStockDetail;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendStockDraftController extends Controller
{
    /**
     * Display a listing of draft send stocks.
     */
    public function index()
    {
        return view('pages.sendStok.draft.index');
    }

    /**
     * Get data for DataTables.
     */
    public function data()
    {
        try {
            $userRoles = auth()->user()->getRoleNames();
            $query = SendStock::with(['fromWarehouse', 'toWarehouse', 'user'])->draft();

            if ($userRoles[0] !== 'master') {
                $query->where('from_warehouse', auth()->user()->warehouse_id);
            }

            $sendStokDrafts = $query->orderBy('id', 'desc')->get();

            // Log for debugging
            Log::info('Draft Send Stock Data', [
                'count' => $sendStokDrafts->count(),
                'user_role' => $userRoles[0],
                'user_warehouse' => auth()->user()->warehouse_id,
                'data' => $sendStokDrafts->toArray()
            ]);

            return response()->json($sendStokDrafts);
        } catch (\Exception $e) {
            Log::error('Error fetching draft send stock data: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created draft send stock.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $fromWarehouse = $user->warehouse_id;
        $toWarehouse = $request->input('to_warehouse');

        // Fetch cart items with products
        $carts = SendStockCart::with('product')->where('user_id', $user->id)->get();

        if ($carts->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
        }

        // Create draft send stock
        $sendStock = SendStock::create([
            'user_id' => $user->id,
            'from_warehouse' => $fromWarehouse,
            'to_warehouse' => $toWarehouse,
            'status' => 'draft',
        ]);

        $sendStockDetails = [];

        foreach ($carts as $cart) {
            $sendStockDetails[] = [
                'send_stock_id' => $sendStock->id,
                'product_id' => $cart->product_id,
                'unit_id' => $cart->unit_id,
                'quantity' => $cart->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all draft details in one query
        SendStockDetail::insert($sendStockDetails);

        // Clear cart
        SendStockCart::where('user_id', $user->id)->delete();

        return redirect()->route('pindah-stok-draft.index')->with('success', 'Draft pindah stok berhasil disimpan.');
    }

    /**
     * Display the specified draft send stock.
     */
    public function show(string $id)
    {
        $sendStockDetail = SendStockDetail::with('product', 'unit')->where('send_stock_id', $id)->get();
        return response()->json($sendStockDetail);
    }

    /**
     * Show the form for editing the specified draft send stock.
     */
    public function edit(string $id)
    {
        $sendStock = SendStock::with(['fromWarehouse', 'toWarehouse'])->findOrFail($id);

        if ($sendStock->status !== 'draft') {
            return redirect()->route('pindah-stok-draft.index')->with('error', 'Hanya draft yang dapat diedit.');
        }

        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        $products = Product::orderBy('id', 'asc')->get();
        $units = Unit::orderBy('id', 'asc')->get();

        // Get existing details and convert to cart format for editing
        $sendStockDetails = SendStockDetail::with('product', 'unit')
            ->where('send_stock_id', $id)
            ->get();

        return view('pages.sendStok.draft.edit', compact('sendStock', 'warehouses', 'products', 'units', 'sendStockDetails'));
    }

    /**
     * Update the specified draft send stock.
     */
    public function update(Request $request, string $id)
    {
        $sendStock = SendStock::findOrFail($id);

        if ($sendStock->status !== 'draft') {
            return redirect()->route('pindah-stok-draft.index')->with('error', 'Hanya draft yang dapat diupdate.');
        }

        $sendStock->update([
            'to_warehouse' => $request->input('to_warehouse'),
        ]);

        return redirect()->route('pindah-stok-draft.index')->with('success', 'Draft berhasil diperbarui.');
    }

    /**
     * Remove the specified draft send stock.
     */
    public function destroy(string $id)
    {
        try {
            $sendStock = SendStock::findOrFail($id);

            if ($sendStock->status !== 'draft') {
                return redirect()->route('pindah-stok-draft.index')->with('error', 'Hanya draft yang dapat dihapus.');
            }

            // Delete related details first
            SendStockDetail::where('send_stock_id', $id)->delete();

            // Delete the draft
            $sendStock->delete();

            return redirect()->route('pindah-stok-draft.index')->with('success', 'Draft berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('pindah-stok-draft.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Complete/Execute a draft send stock.
     */
    public function complete(string $id)
    {
        try {
            $sendStock = SendStock::findOrFail($id);

            if ($sendStock->status !== 'draft') {
                return redirect()->route('pindah-stok-draft.index')->with('error', 'Hanya draft yang dapat diselesaikan.');
            }

            // Fetch draft details
            $sendStockDetails = SendStockDetail::with('product')->where('send_stock_id', $id)->get();

            // Fetch all related inventory records
            $productIds = $sendStockDetails->pluck('product.id');
            $inventories = Inventory::whereIn('product_id', $productIds)
                ->where('warehouse_id', $sendStock->from_warehouse)
                ->get()
                ->keyBy('product_id');

            $stockErrors = [];

            // Validate stock availability
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

                // Check available stock
                $fromInventory = $inventories[$product->id] ?? null;

                if (!$fromInventory || $fromInventory->quantity < $quantityEceran) {
                    $stockErrors[] = "Stok tidak mencukupi untuk {$product->name}. Dibutuhkan: $quantityEceran, Tersedia: " . ($fromInventory->quantity ?? 0);
                }
            }

            if (!empty($stockErrors)) {
                return redirect()->back()->with('error', implode('<br>', $stockErrors));
            }

            // Execute the stock transfer
            foreach ($sendStockDetails as $detail) {
                $product = $detail->product;
                $unit = $detail->unit_id;
                $quantity = $detail->quantity;

                $quantityEceran = match ($unit) {
                    $product->unit_dus => $quantity * $product->dus_to_eceran,
                    $product->unit_pak => $quantity * $product->pak_to_eceran,
                    default => $quantity
                };

                // Deduct stock from source warehouse
                Inventory::where('id', $inventories[$product->id]->id)->decrement('quantity', $quantityEceran);

                // Increase stock in destination warehouse
                Inventory::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $sendStock->to_warehouse],
                    ['quantity' => DB::raw("quantity + $quantityEceran")]
                );
            }

            // Update status to completed
            $sendStock->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return redirect()->route('pindah-stok-draft.index')->with('success', 'Draft berhasil diselesaikan dan stok telah dipindahkan.');
        } catch (\Exception $e) {
            return redirect()->route('pindah-stok-draft.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Add item to draft cart.
     */
    public function addCart(Request $request)
    {
        $existingCart = SendStockCart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        $product = Product::find($request->product_id);

        $quantityDus = $request->quantity_dus ?? 0;
        $quantityPak = $request->quantity_pak ?? 0;
        $quantityEceran = $request->quantity_eceran ?? 0;

        if ($existingCart) {
            $totalQuantity = $quantityDus + $quantityPak + $quantityEceran;
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

    /**
     * Remove item from draft cart.
     */
    public function destroyCart($id)
    {
        try {
            // Try to find in SendStockCart first (for create/add to cart scenario)
            $cart = SendStockCart::find($id);
            if ($cart) {
                $cart->delete();
                return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang');
            }

            // If not found in cart, try SendStockDetail (for edit draft scenario)
            $detail = SendStockDetail::find($id);
            if ($detail) {
                // Check if this detail belongs to a draft
                $sendStock = SendStock::find($detail->send_stock_id);
                if ($sendStock && $sendStock->status === 'draft') {
                    $detail->delete();
                    return redirect()->back()->with('success', 'Item berhasil dihapus dari draft');
                } else {
                    return redirect()->back()->with('error', 'Tidak dapat menghapus item dari transaksi yang sudah selesai');
                }
            }

            return redirect()->back()->with('error', 'Item tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Add item to existing draft (for edit functionality).
     */
    public function addToExistingDraft(Request $request, $draftId)
    {
        try {
            $sendStock = SendStock::findOrFail($draftId);

            if ($sendStock->status !== 'draft') {
                return redirect()->back()->with('error', 'Hanya draft yang dapat diedit.');
            }

            $product = Product::find($request->product_id);
            $quantityDus = $request->quantity_dus ?? 0;
            $quantityPak = $request->quantity_pak ?? 0;
            $quantityEceran = $request->quantity_eceran ?? 0;

            // Check if product already exists in this draft
            $existingDetail = SendStockDetail::where('send_stock_id', $draftId)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingDetail) {
                // Update existing detail
                $totalQuantity = $quantityDus + $quantityPak + $quantityEceran;
                $existingDetail->quantity += $totalQuantity;
                $existingDetail->save();
            } else {
                // Create new details for each unit type
                if ($quantityDus > 0) {
                    SendStockDetail::create([
                        'send_stock_id' => $draftId,
                        'product_id' => $request->product_id,
                        'unit_id' => $product->unit_dus,
                        'quantity' => $quantityDus,
                    ]);
                }

                if ($quantityPak > 0) {
                    SendStockDetail::create([
                        'send_stock_id' => $draftId,
                        'product_id' => $request->product_id,
                        'unit_id' => $product->unit_pak,
                        'quantity' => $quantityPak,
                    ]);
                }

                if ($quantityEceran > 0) {
                    SendStockDetail::create([
                        'send_stock_id' => $draftId,
                        'product_id' => $request->product_id,
                        'unit_id' => $product->unit_eceran,
                        'quantity' => $quantityEceran,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke draft');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
