<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\Purchase;
use App\Models\PurchaseRetur;
use App\Models\Sell;
use App\Models\SellRetur;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('pages.report.index', compact('warehouses'));
    }

    public function data(Request $request)
    {
        $warehouse = $request->input('warehouse');

        if ($warehouse) {
            // Fetch data for the specified warehouse
            $purchase = Purchase::where('warehouse_id', $warehouse)
                ->where('status', '!=', 'hutang')
                ->orderBy('created_at', 'desc')
                ->get();
            $sell = Sell::where('warehouse_id', $warehouse)
                ->where('status', '!=', 'hutang')
                ->orderBy('created_at', 'desc')
                ->get();
            $kas = Kas::where('warehouse_id', $warehouse)
                ->orderBy('created_at', 'desc')
                ->get();
            // Fetch data for all warehouses
        } else {
            $purchase = Purchase::orderBy('created_at', 'desc')->where('status', '!=', 'hutang')->get();
            $sell = Sell::orderBy('created_at', 'desc')->where('status', '!=', 'hutang')->get();
            $kas = Kas::orderBy('created_at', 'desc')->get();
        }

        $combinedData = collect([])
            ->concat(
                $purchase->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->format('d-m-Y'),
                        'untuk' => 'Pembelian',
                        'keterangan' => $item->order_number,
                        'masuk' => 0,
                        'keluar' => $item->grand_total,
                    ];
                }),
            )
            ->concat(
                $sell->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->format('d-m-Y'),
                        'untuk' => 'Penjualan',
                        'keterangan' => $item->order_number,
                        'masuk' => $item->grand_total,
                        'keluar' => 0,
                    ];
                }),
            )
            ->concat(
                $kas->map(function ($item) {
                    if ($item->type == 'Kas Masuk') {
                        $untuk = 'Kas Masuk';
                    } else {
                        $untuk = 'Kas Keluar';
                    }
                    return [
                        'tanggal' => $item->created_at->format('d-m-Y'),
                        'untuk' => $untuk,
                        'keterangan' => $item->description,
                        'masuk' => $untuk == 'Kas Masuk' ? $item->amount : 0,
                        'keluar' => $untuk == 'Kas Keluar' ? $item->amount : 0,
                    ];
                }),
            );


        return response()->json($combinedData);
    }
}
