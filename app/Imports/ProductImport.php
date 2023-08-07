<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // HeadingRowFormatter::default('none');
        $warehouseId = auth()->user()->warehouse_id;
        $products = Product::create([
            'group' => $row['kelompok'],
            'name' => $row['nama_barang'],
            'unit_dus' => $row['satuan_dus'],
            'unit_pak' => $row['satuan_pak'],
            'unit_eceran' => $row['satuan_eceran'],
            'barcode_dus' => $row['barcode_dus'],
            'barcode_pak' => $row['barcode_pak'],
            'barcode_eceran' => $row['barcode_eceran'],
            'price_dus' => $row['harga_dus'],
            'price_pak' => $row['harga_pak'],
            'price_eceran' => $row['harga_eceran'],
            'sales_price' => $row['harga_sales'],
            'lastest_price_eceran' => $row['harga_eceran_terakhir'],
            'dus_to_eceran' => $row['dus_ke_eceran'],
            'pak_to_eceran' => $row['pak_ke_eceran'],
            'hadiah' => $row['hadiah'],
        ]);

        // Save inventory data for unit_dus if unit_dus is not null
        Inventory::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $products->id,
            'quantity' => $row['stok'] ?? 0,
        ]);

        return $products;
    }
}
