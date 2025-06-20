<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Product::select(array_diff(
            Schema::getColumnListing('products'),
            ['created_at', 'updated_at']
        ))->get();
    }
}
