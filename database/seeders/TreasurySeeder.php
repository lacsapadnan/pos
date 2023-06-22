<?php

namespace Database\Seeders;

use App\Models\Treasury;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreasurySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Treasury::create([
            'name' => 'Kas Kecil',
        ]);

        Treasury::create([
            'name' => 'Kas Besar',
        ]);
    }
}
