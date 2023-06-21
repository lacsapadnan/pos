<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $warehouseId = auth()->user()->warehouse_id;
        $products = Product::create([
            'group' => $row[0],
            'name' => $row[1],
            'unit_dus' => $row[2],
            'unit_pak' => $row[3],
            'unit_eceran' => $row[4],
            'barcode_dus' => $row[5],
            'barcode_pak' => $row[6],
            'barcode_eceran' => $row[7],
            'price_dus' => $row[8],
            'price_pak' => $row[9],
            'price_eceran' => $row[10],
            'sales_price' => $row[11],
            'lastest_price_eceran' => $row[12],
            'dus_to_eceran' => $row[13],
            'pak_to_eceran' => $row[14],
            'hadiah' => $row[15],
        ]);

        // Save inventory data for unit_dus if unit_dus is not null
        Inventory::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $products->id,
            'quantity' => $row[16] ?? 0,
        ]);

        return $products;
    }
}
