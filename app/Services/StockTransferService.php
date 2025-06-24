<?php

namespace App\Services;

use App\Models\ProductReport;
use App\Models\Unit;
use App\Models\Warehouse;

class StockTransferService
{
    /**
     * Create ProductReport entries for stock transfer tracking
     */
    public function createStockTransferReports($sendStock, $transferItems)
    {
        // Get warehouse names for description
        $fromWarehouse = Warehouse::find($sendStock->from_warehouse);
        $toWarehouse = Warehouse::find($sendStock->to_warehouse);
        $description = "PINDAH STOK FROM {$fromWarehouse->name} KE {$toWarehouse->name}";

        foreach ($transferItems as $item) {
            // Handle both cart items and detail items
            $product = $item->product ?? $item;
            $unitId = $item->unit_id;
            $quantity = $item->quantity;
            $unit = Unit::find($unitId);

            // Determine unit type
            $unitType = 'ECERAN'; // default
            if ($unitId == $product->unit_dus) {
                $unitType = 'DUS';
            } elseif ($unitId == $product->unit_pak) {
                $unitType = 'PAK';
            }

            // Get price based on unit type
            $price = match ($unitType) {
                'DUS' => $product->price_dus ?? 0,
                'PAK' => $product->price_pak ?? 0,
                default => $product->price_eceran ?? 0
            };

            // Create KELUAR record for source warehouse
            ProductReport::create([
                'product_id' => $product->id,
                'warehouse_id' => $sendStock->from_warehouse,
                'user_id' => $sendStock->user_id,
                'customer_id' => null,
                'supplier_id' => null,
                'unit' => $unit->name,
                'unit_type' => $unitType,
                'qty' => $quantity,
                'price' => $price,
                'type' => 'PINDAH STOK',
                'for' => 'KELUAR',
                'description' => $description,
            ]);

            // Create MASUK record for destination warehouse
            ProductReport::create([
                'product_id' => $product->id,
                'warehouse_id' => $sendStock->to_warehouse,
                'user_id' => $sendStock->user_id,
                'customer_id' => null,
                'supplier_id' => null,
                'unit' => $unit->name,
                'unit_type' => $unitType,
                'qty' => $quantity,
                'price' => $price,
                'type' => 'PINDAH STOK',
                'for' => 'MASUK',
                'description' => $description,
            ]);
        }
    }

    /**
     * Delete ProductReport entries for stock transfer
     */
    public function deleteStockTransferReports($sendStock, $productIds)
    {
        $fromWarehouse = Warehouse::find($sendStock->from_warehouse);
        $toWarehouse = Warehouse::find($sendStock->to_warehouse);

        ProductReport::where('type', 'PINDAH STOK')
            ->where('description', 'LIKE', '%' . $fromWarehouse->name . '%')
            ->where('description', 'LIKE', '%' . $toWarehouse->name . '%')
            ->whereIn('product_id', $productIds)
            ->delete();
    }
}
