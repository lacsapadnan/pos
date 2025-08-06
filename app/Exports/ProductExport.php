<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
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

    /**
     * @return array
     */
    public function headings(): array
    {
        $columns = array_diff(
            Schema::getColumnListing('products'),
            ['created_at', 'updated_at']
        );

        // Convert column names to readable headers
        return array_map(function ($column) {
            return ucwords(str_replace('_', ' ', $column));
        }, $columns);
    }
}
