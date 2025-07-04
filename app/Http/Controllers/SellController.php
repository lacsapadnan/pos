<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Sell;
use App\Models\SellCart;
use App\Models\SellCartDraft;
use App\Models\SellDetail;
use App\Models\SellRetur;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CashflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\ImagickEscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use DataTables;

class SellController extends Controller
{
    protected $cashflowService;

    public function __construct(CashflowService $cashflowService)
    {
        $this->cashflowService = $cashflowService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masters = User::role('master')->get();
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.sell.index', compact('masters', 'warehouses', 'users'));
    }

    public function data(Request $request)
    {
        $role = auth()->user()->getRoleNames();
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date') ?? date('Y-m-d');
        $toDate = $request->input('to_date') ?? date('Y-m-d');
        $warehouse = $request->input('warehouse');
        $export = $request->input('export');
        $search = $request->input('search');

        $query = Sell::with([
            'warehouse',
            'customer',
            'cashier',
            'details.product.unit_dus',
            'details.product.unit_pak',
            'details.product.unit_eceran',
            'details.unit'
        ])
            ->where('status', '!=', 'draft')
            ->orderBy('id', 'desc');

        if ($role[0] !== 'master') {
            $query->where('warehouse_id', auth()->user()->warehouse_id)
                ->where('cashier_id', auth()->id());
        }

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $query->where('cashier_id', $user_id);
        }

        if ($fromDate && $toDate) {
            $endDate = \Carbon\Carbon::parse($toDate)->endOfDay();
            $query->whereBetween('created_at', [$fromDate, $endDate]);
        }

        // Apply search if provided
        if (!empty($search) && !empty($search['value'])) {
            $searchValue = $search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('order_number', 'like', "%{$searchValue}%")
                    ->orWhereHas('customer', function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('cashier', function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhere('payment_method', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        // If export parameter is set, return all data without pagination
        if ($export) {
            $sells = $query->get();
            return response()->json($sells);
        }

        return DataTables::of($query)
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $inventories = Inventory::with(['product' => function ($query) {
            $query->where('isShow', true)
                ->with(['unit_dus', 'unit_pak', 'unit_eceran']);
        }])
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->get();

        $products = Product::with(['unit_dus', 'unit_pak', 'unit_eceran'])
            ->where('isShow', true)
            ->get();
        $customers = Customer::all();
        $today = date('Ymd');
        $year = substr($today, 2, 2);
        $today = substr($today, 2);
        $warehouseId = auth()->user()->warehouse_id;
        $userId = auth()->id();

        $lastOrder = Sell::where('cashier_id', $userId)
            ->where('warehouse_id', $warehouseId)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastOrder) {
            // Extract the numerical part of the order number and increment it
            $lastOrderNumberPart = explode('-', $lastOrder->order_number);
            $lastOrderNumber = intval(end($lastOrderNumberPart));
            $newOrderNumber = $lastOrderNumber + 1;
        } else {
            // Reset the order number to 1
            $newOrderNumber = 1;
        }

        // Format the new order number with leading zeros
        $formattedOrderNumber = str_pad($newOrderNumber, 4, '0', STR_PAD_LEFT);

        // Generate the order number string with warehouseId in the middle
        $orderNumber = "PJ-" . $today . "-" . $warehouseId . auth()->id() . "-" . $formattedOrderNumber;
        $cart = SellCart::with('product', 'unit')->orderBy('id', 'desc')
            ->where('cashier_id', auth()->id())
            ->get();
        $subtotal = 0;
        foreach ($cart as $c) {
            $subtotal += ($c->price * $c->quantity) - $c->diskon;
        }
        $masters = User::role('master')->get();
        return view('pages.sell.create', compact('inventories', 'products', 'cart', 'subtotal', 'customers', 'orderNumber', 'masters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sellCart = SellCart::with(['product', 'unit'])->where('cashier_id', auth()->id())->get();

        // Validate cart has items if not a draft
        if ($sellCart->isEmpty() && $request->status !== 'draft') {
            return redirect()->back()->withInput()->withErrors('Keranjang penjualan kosong');
        }

        $transfer = (int)str_replace(',', '', $request->transfer ?? 0);
        $cash = (int)str_replace(',', '', $request->cash ?? 0);
        $grandtotal = (int)preg_replace('/[,.]/', '', $request->grand_total);
        $pay = $transfer + $cash;

        if ($request->status === 'draft') {
            $status = 'draft';
        } elseif ($pay < $grandtotal) {
            $status = 'piutang';
        } else {
            $status = 'lunas';
        }

        $grandTotalValid = 0;
        foreach ($sellCart as $sc) {
            $grandTotalValid += (int)preg_replace('/[,.]/', '', ($sc->price * $sc->quantity) - $sc->diskon);
        }

        if ($grandtotal !== $grandTotalValid) {
            return redirect()->back()->withInput()->withErrors('Terjadi Kesalahan Kalkulasi Total');
        }

        try {
            DB::beginTransaction();

            $sell = Sell::create([
                'cashier_id' => auth()->id(),
                'warehouse_id' => auth()->user()->warehouse_id,
                'order_number' => $request->order_number,
                'customer_id' => $request->customer,
                'subtotal' => preg_replace('/[,.]/', '', $request->subtotal),
                'grand_total' => preg_replace('/[,.]/', '', $request->grand_total),
                'cash' => preg_replace('/[,.]/', '', $cash),
                'transfer' => preg_replace('/[,.]/', '', $transfer),
                'pay' => preg_replace('/[,.]/', '', $pay),
                'change' => preg_replace('/[,.]/', '', $request->change ?? 0),
                'transaction_date' => Carbon::createFromFormat('d/m/Y', $request->transaction_date)->format('Y-m-d'),
                'payment_method' => $request->payment_method,
                'status' => $status,
            ]);

            $customer = Customer::find($request->customer);

            if ($request->status == 'draft') {
                foreach ($sellCart as $sc) {
                    SellCartDraft::create([
                        'sell_id' => $sell->id,
                        'cashier_id' => auth()->id(),
                        'product_id' => $sc->product_id,
                        'unit_id' => $sc->unit_id,
                        'quantity' => $sc->quantity,
                        'price' => $sc->price,
                        'diskon' => $sc->diskon,
                    ]);
                }

                // Clear the cart after creating draft
                SellCart::where('cashier_id', auth()->id())->delete();
            } else {
                // Create sell details first
                foreach ($sellCart as $sc) {
                    $detail = SellDetail::create([
                        'sell_id' => $sell->id,
                        'product_id' => $sc->product_id,
                        'unit_id' => $sc->unit_id,
                        'quantity' => $sc->quantity,
                        'price' => $sc->price,
                        'diskon' => $sc->diskon,
                    ]);

                    // Use already loaded relationships from eager loading
                    $unit = $sc->unit;
                    $product = $sc->product;

                    if ($sc->unit_id == $product->unit_dus) {
                        $unitType = 'DUS';
                    } elseif ($sc->unit_id == $product->unit_pak) {
                        $unitType = 'PAK';
                    } elseif ($sc->unit_id == $product->unit_eceran) {
                        $unitType = 'ECERAN';
                    }

                    ProductReport::create([
                        'product_id' => $sc->product_id,
                        'warehouse_id' => auth()->user()->warehouse_id,
                        'user_id' => auth()->id(),
                        'customer_id' => $request->customer,
                        'unit' => $unit->name,
                        'unit_type' => $unitType,
                        'qty' => $sc->quantity,
                        'price' => $sc->price - $sc->diskon,
                        'for' => 'KELUAR',
                        'type' => 'PENJUALAN',
                        'description' => 'Penjualan ' . $sell->order_number,
                    ]);
                }

                // Only create cashflow if there's actual payment
                if ($pay > 0 && $request->payment_method) {
                    $this->cashflowService->handleSalePayment(
                        warehouseId: auth()->user()->warehouse_id,
                        orderNumber: $sell->order_number,
                        customerName: $customer->name,
                        paymentMethod: $request->payment_method,
                        cash: $cash,
                        transfer: $transfer,
                        change: $sell->change
                    );
                }

                // Move cart deletion to end of transaction
                SellCart::where('cashier_id', auth()->id())->delete();
            }

            DB::commit();

            // Verify sell details were created if not a draft
            if ($request->status !== 'draft') {
                $detailCount = SellDetail::where('sell_id', $sell->id)->count();
                if ($detailCount === 0) {
                    return redirect()->back()->withInput()
                        ->withErrors('Terjadi kesalahan: Detail penjualan tidak tersimpan');
                }
            }

            if ($request->status != 'draft') {
                try {
                    // Add a small delay to ensure database operations are fully committed
                    sleep(1);

                    $printUrl = route('penjualan.print', $sell->id);
                    $script = "<script>
                        setTimeout(function() {
                            window.open('$printUrl', '_blank');
                        }, 500);
                    </script>";
                    return Response::make($script . '<script>setTimeout(function() { window.location.href = "' . route('penjualan.index') . '"; }, 1000);</script>');
                } catch (\Throwable $th) {
                    return redirect()->route('penjualan.index')->withErrors('Transaksi berhasil disimpan, tetapi gagal mencetak struk');
                }
            } else {
                return redirect()->route('penjualan.index');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->withErrors('Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function printReceipt($id)
    {
        $sell = Sell::with('warehouse', 'customer', 'cashier')->find($id);
        $details = SellDetail::with('product', 'unit')->where('sell_id', $id)->get();

        // Get return data for this sale
        $sellReturs = SellRetur::with(['detail.product', 'detail.unit'])
            ->where('sell_id', $id)
            ->get();

        // Create a collection of returned items with their quantities
        $returnedItems = collect();
        foreach ($sellReturs as $sellRetur) {
            foreach ($sellRetur->detail as $returDetail) {
                $key = $returDetail->product_id . '_' . $returDetail->unit_id;
                if ($returnedItems->has($key)) {
                    $returnedItems[$key]['qty'] += $returDetail->qty;
                } else {
                    $returnedItems[$key] = [
                        'product_id' => $returDetail->product_id,
                        'unit_id' => $returDetail->unit_id,
                        'product_name' => $returDetail->product->name,
                        'unit_name' => $returDetail->unit->name,
                        'qty' => $returDetail->qty,
                        'price' => $returDetail->price
                    ];
                }
            }
        }

        $totalQuantity = 0;
        $totalQuantity += $details->count();
        $pdf = Pdf::loadView('pages.sell.print', compact('sell', 'details', 'totalQuantity', 'returnedItems'));

        // Save the PDF to a file
        $pdf->save("receipt.pdf");

        // Convert the PDF to an image using Imagick
        $imagick = new \Imagick();
        $imagick->readImage("receipt.pdf[0]"); // Read the first page of the PDF
        $imagick->setImageFormat('png');
        $imagick->writeImage('receipt.png');

        $connector = new FilePrintConnector("php://stdout");
        $printer = new Printer($connector);

        // Load the image
        $img = EscposImage::load("receipt.png", false);

        // Print the image
        $printer->graphics($img);

        // Close printer
        $printer->close();
    }

    public function checkCustomerStatus(Request $request)
    {
        $customerId = $request->input('customer_id'); // Get the customer ID from the request

        // Check if the customer has 'piutang' status
        $customer = Sell::where('customer_id', $customerId)
            ->where('status', 'piutang')
            ->first();

        if ($customer) {
            return response()->json(['status' => 'piutang']);
        } else {
            // Customer does not have 'piutang' status
            return response()->json(['status' => 'not_piutang']);
        }
    }

    public function validateMasterPassword(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        // check the password request is same with user password
        if (password_verify($request->password, $user->password)) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sellDetail = SellDetail::with([
            'product' => function ($query) {
                $query->with(['unit_dus', 'unit_pak', 'unit_eceran']);
            },
            'unit',
            'sell' => function ($query) {
                $query->with(['warehouse', 'customer']);
            }
        ])->where('sell_id', $id)->get();

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
        $sell = Sell::with('details')->find($id);

        if (!$sell) {
            return redirect()->back()->with('error', 'Data penjualan tidak ditemukan');
        }

        // Delete associated cashflows first
        $deletedCashflows = $this->cashflowService->deleteAllSaleCashflows($sell->order_number);

        // Then delete the sell record
        $sell->delete();

        $message = "Data penjualan berhasil dihapus";
        if ($deletedCashflows > 0) {
            $message .= " beserta {$deletedCashflows} record cashflow terkait";
        }

        return redirect()->back()->with('success', $message);
    }

    public function addCart(Request $request)
    {
        // $inputRequests = $request->input('requests');
        $requests = $request->input('requests');


        try {
            DB::beginTransaction();

            foreach ($requests as $inputRequest) {
                $productId = $inputRequest['product_id'];

                // Process quantity_dus if it exists
                if (isset($inputRequest['quantity_dus']) && $inputRequest['quantity_dus']) {
                    $this->processCartItem($productId, $inputRequest['quantity_dus'], $inputRequest['unit_dus'], $inputRequest['price_dus'], $inputRequest['diskon_dus'] ?? 0);
                    $this->decreaseInventory($productId, $inputRequest['quantity_dus'], $inputRequest['unit_dus']);
                }

                // Process quantity_pak if it exists
                if (isset($inputRequest['quantity_pak']) && $inputRequest['quantity_pak']) {
                    $this->processCartItem($productId, $inputRequest['quantity_pak'], $inputRequest['unit_pak'], $inputRequest['price_pak'], $inputRequest['diskon_pak'] ?? 0);
                    $this->decreaseInventory($productId, $inputRequest['quantity_pak'], $inputRequest['unit_pak']);
                }

                // Process quantity_eceran if it exists
                if (isset($inputRequest['quantity_eceran']) && $inputRequest['quantity_eceran']) {
                    $this->processCartItem($productId, $inputRequest['quantity_eceran'], $inputRequest['unit_eceran'], $inputRequest['price_eceran'], $inputRequest['diskon_eceran'] ?? 0);
                    $this->decreaseInventory($productId, $inputRequest['quantity_eceran'], $inputRequest['unit_eceran']);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to add items to cart.'], 500);
        }

        return response()->json(['success' => 'Items added to cart successfully.'], 200);
    }

    private function processCartItem($productId, $quantity, $unitId, $price, $discount)
    {
        // Calculate the total price based on the unit price and quantity
        $totalPrice = $price * $quantity;

        // Apply the discount to the total price if the discount is provided
        if ($discount > 0) {
            $totalPrice -= $discount;
        }

        $existingCart = SellCart::where('cashier_id', auth()->id())
            ->where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->first();

        if ($existingCart) {
            $existingCart->quantity += $quantity;
            $existingCart->save();
        } else {
            SellCart::create([
                'cashier_id' => auth()->id(),
                'product_id' => $productId,
                'unit_id' => $unitId,
                'quantity' => $quantity,
                'price' => $price,
                'diskon' => $discount,
            ]);
        }
    }

    private function decreaseInventory($productId, $quantity, $unitId)
    {
        $product = Product::find($productId);
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->first();

        if ($unitId == $product->unit_dus) {
            $inventory->quantity -= $quantity * $product->dus_to_eceran;
        } elseif ($unitId == $product->unit_pak) {
            $inventory->quantity -= $quantity * $product->pak_to_eceran;
        } elseif ($unitId == $product->unit_eceran) {
            $inventory->quantity -= $quantity;
        }

        $inventory->save();
    }

    public function destroyCart(Request $request, $id)
    {
        $sellCart = SellCart::where('product_id', $request->product_id)
            ->where('id', $id)
            ->first();

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

        // Get return data for this sale
        $sellReturs = SellRetur::with(['detail.product', 'detail.unit'])
            ->where('sell_id', $id)
            ->get();

        // Create a collection of returned items with their quantities
        $returnedItems = collect();
        foreach ($sellReturs as $sellRetur) {
            foreach ($sellRetur->detail as $returDetail) {
                $key = $returDetail->product_id . '_' . $returDetail->unit_id;
                if ($returnedItems->has($key)) {
                    $returnedItems[$key]['qty'] += $returDetail->qty;
                } else {
                    $returnedItems[$key] = [
                        'product_id' => $returDetail->product_id,
                        'unit_id' => $returDetail->unit_id,
                        'product_name' => $returDetail->product->name,
                        'unit_name' => $returDetail->unit->name,
                        'qty' => $returDetail->qty,
                        'price' => $returDetail->price
                    ];
                }
            }
        }

        $totalQuantity = 0;
        $totalQuantity += $details->count();
        $pdf = Pdf::loadView('pages.sell.print', compact('sell', 'details', 'totalQuantity', 'returnedItems'));
        return response()->stream(function () use ($pdf) {
            echo $pdf->output();
        }, 200, [
            'attachment' => false,
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Transaksi-' . $sell->order_number . '.pdf"'
        ]);
    }

    public function credit()
    {
        $warehouses = Warehouse::with('users')->get();
        $users = User::with('roles', 'warehouse')->get();
        return view('pages.sell.credit', compact('warehouses', 'users'));
    }

    public function dataCredit(Request $request)
    {
        $userRoles = auth()->user()->getRoleNames();
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $warehouse = $request->input('warehouse');

        $query = Sell::with([
            'warehouse',
            'customer',
            'cashier',
            'details.product.unit_dus',
            'details.product.unit_pak',
            'details.product.unit_eceran',
            'details.unit'
        ])->where('status', 'piutang');

        if ($userRoles[0] != 'master') {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $query->where('cashier_id', $user_id);
        }

        if ($fromDate && $toDate) {
            $endDate = Carbon::parse($toDate)->endOfDay();
            $query->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        return response()->json($query->get());
    }

    public function payCredit(Request $request)
    {
        $validated = $request->validate([
            'sell_id' => 'required|integer',
            'payment' => 'required|string',
        ]);

        $sell = Sell::with('customer')->find($validated['sell_id']);

        if (!$sell) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sale not found'
            ]);
        }

        $grandTotal = (int) preg_replace('/[,.]/', '', $sell->grand_total);
        $currentPay = (int) preg_replace('/[,.]/', '', $sell->pay);
        $paymentMethod = $validated['payment'];

        // Initialize payment variables
        $payment = 0;
        $paymentCash = 0;
        $paymentTransfer = 0;
        $potongan = (int) preg_replace('/[,.]/', '', $request->potongan ?? 0);

        if ($paymentMethod === 'split') {
            $paymentCash = (int) preg_replace('/[,.]/', '', $request->pay_credit_cash ?? 0);
            $paymentTransfer = (int) preg_replace('/[,.]/', '', $request->pay_credit_transfer ?? 0);
            $payment = $paymentCash + $paymentTransfer;

            // Validate split payment
            if ($paymentCash < 0 || $paymentTransfer < 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembayaran tidak boleh kurang dari 0'
                ]);
            }
        } else {
            $payment = (int) preg_replace('/[,.]/', '', $request->pay ?? 0);
            if ($payment <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembayaran tidak boleh 0 atau kurang'
                ]);
            }
        }

        $sisaHutang = $grandTotal - $currentPay;

        // Validate total payment (payment + potongan) against remaining debt
        if (($payment + $potongan) > $sisaHutang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Total pembayaran dan potongan tidak boleh lebih dari sisa piutang'
            ]);
        }

        $sell->pay = $currentPay + $payment + $potongan;

        if ($sell->pay >= $grandTotal) {
            $sell->status = 'lunas';
        }

        $sell->save();

        // Handle cashflow using service
        $this->cashflowService->handleCreditPayment(
            warehouseId: $sell->warehouse_id,
            orderNumber: $sell->order_number,
            customerName: $sell->customer->name,
            paymentMethod: $paymentMethod,
            payment: $payment,
            paymentCash: $paymentCash,
            paymentTransfer: $paymentTransfer,
            potongan: $potongan,
            keterangan: $request->keterangan ?? ''
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran piutang berhasil'
        ]);
    }
}
