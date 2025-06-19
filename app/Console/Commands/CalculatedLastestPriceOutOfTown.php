<?php

namespace App\Console\Commands;

use App\Models\PurchaseDetail;
use Illuminate\Console\Command;

class CalculatedLastestPriceOutOfTown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculated-lastest-price-out-of-town';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate latest price for out of town warehouses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $purchaseDetails = PurchaseDetail::with('product', 'unit', 'purchase.warehouse')
            ->whereHas('purchase.warehouse', function ($query) {
                $query->where('isOutOfTown', true);
            })
            ->get();

        foreach ($purchaseDetails as $purchaseDetail) {
            if ($purchaseDetail->unit_id == $purchaseDetail->product->unit_dus && $purchaseDetail->product->dus_to_eceran) {
                $purchaseDetail->product->update([
                    'lastest_price_eceran_out_of_town' => $purchaseDetail->product->price_sell_dus_out_of_town / $purchaseDetail->product->dus_to_eceran
                ]);
            } elseif ($purchaseDetail->unit_id == $purchaseDetail->product->unit_pak && $purchaseDetail->product->pak_to_eceran) {
                $purchaseDetail->product->update([
                    'lastest_price_eceran_out_of_town' => $purchaseDetail->product->price_sell_pak_out_of_town / $purchaseDetail->product->pak_to_eceran
                ]);
            } elseif ($purchaseDetail->unit_id == $purchaseDetail->product->unit_eceran) {
                $purchaseDetail->product->update([
                    'lastest_price_eceran_out_of_town' => $purchaseDetail->product->price_sell_eceran_out_of_town
                ]);
            }
        }

        $this->info('Successfully calculated latest prices for out of town warehouses');
    }
}
