<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unit = [
            "DUS",
            "PAK",
            "PCS",
            "RCG",
            "BAL",
            "IKAT",
            "PETI",
            "KG",
            "LSN",
            "DRG",
            "ONS",
            "UNIT",
        ];
        // create unit
        foreach ($unit as $u) {
            Unit::create([
                'name' => $u,
            ]);
        }
    }
}
