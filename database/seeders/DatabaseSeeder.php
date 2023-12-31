<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(WarehouseSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(UsersAndRolesSeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(TreasurySeeder::class);
    }
}
