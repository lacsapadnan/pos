<?php

namespace App\Console\Commands;

use App\Models\PurchaseDetail;
use Illuminate\Console\Command;

class CalculatedLastestPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculated-lastest-price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $purchaseDetails = PurchaseDetail::with('product', 'unit')->get();
        foreach ($purchaseDetails as $purchaseDetail) {
            if ($purchaseDetail->unit_id == $purchaseDetail->product->unit_dus && $purchaseDetail->product->dus_to_eceran != 0) {
                $purchaseDetail->product->update([
                    'lastest_price_eceran' => $purchaseDetail->total_price / $purchaseDetail->product->dus_to_eceran
                ]);
            } elseif ($purchaseDetail->unit_id == $purchaseDetail->product->unit_pak && $purchaseDetail->product->pak_to_eceran != 0) {
                $purchaseDetail->product->update([
                    'lastest_price_eceran' => $purchaseDetail->total_price / $purchaseDetail->product->pak_to_eceran
                ]);
            } elseif ($purchaseDetail->unit_id == $purchaseDetail->product->unit_eceran) {
                $purchaseDetail->product->update([
                    'lastest_price_eceran' => $purchaseDetail->total_price
                ]);
            }
        }

        $this->info('Success');
    }
}
