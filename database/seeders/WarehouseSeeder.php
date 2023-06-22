<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouseName = ['Cabang A', 'Cabang B', 'Cabang C', 'Cabang D', 'Cabang E'];
        $warehouseAddress = ['Jl. A', 'Jl. B', 'Jl. C', 'Jl. D', 'Jl. E'];
        $warehousePhone = ['08123456789', '08123456789', '08123456789', '08123456789', '08123456789'];

        for ($i = 0; $i < count($warehouseName); $i++) {
            Warehouse::create([
                'name' => $warehouseName[$i],
                'address' => $warehouseAddress[$i],
                'phone' => $warehousePhone[$i],
            ]);
        }
    }
}
