<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CRUD warehouse permissions
        Permission::create(['name' => 'create warehouse']);
        Permission::create(['name' => 'read warehouse']);
        Permission::create(['name' => 'update warehouse']);
        Permission::create(['name' => 'delete warehouse']);

        // CRUD product permissions
        Permission::create(['name' => 'create product']);
        Permission::create(['name' => 'read product']);
        Permission::create(['name' => 'update product']);
        Permission::create(['name' => 'delete product']);
        Permission::create(['name' => 'import product']);

        // CRUD supplier permissions
        Permission::create(['name' => 'create supplier']);
        Permission::create(['name' => 'read supplier']);
        Permission::create(['name' => 'update supplier']);
        Permission::create(['name' => 'delete supplier']);
        Permission::create(['name' => 'import supplier']);

        // CRUD customer permissions
        Permission::create(['name' => 'create customer']);
        Permission::create(['name' => 'read customer']);
        Permission::create(['name' => 'update customer']);
        Permission::create(['name' => 'delete customer']);
        Permission::create(['name' => 'import customer']);

        // CRUD unit permissions
        Permission::create(['name' => 'create unit']);
        Permission::create(['name' => 'read unit']);
        Permission::create(['name' => 'update unit']);
        Permission::create(['name' => 'delete unit']);

        // CRUD tresury permissions
        Permission::create(['name' => 'create tresury']);
        Permission::create(['name' => 'read tresury']);
        Permission::create(['name' => 'update tresury']);
        Permission::create(['name' => 'delete tresury']);

        // CRUD inventory permissions
        Permission::create(['name' => 'create inventory']);
        Permission::create(['name' => 'read inventory']);
        Permission::create(['name' => 'update inventory']);
        Permission::create(['name' => 'delete inventory']);

        // CRUD user permissions
        Permission::create(['name' => 'create user']);
        Permission::create(['name' => 'read user']);
        Permission::create(['name' => 'update user']);
        Permission::create(['name' => 'delete user']);

        // CRUD role permissions
        Permission::create(['name' => 'create role']);
        Permission::create(['name' => 'read role']);
        Permission::create(['name' => 'update role']);
        Permission::create(['name' => 'delete role']);

        // CRUD permission permissions
        Permission::create(['name' => 'create permission']);
        Permission::create(['name' => 'read permission']);
        Permission::create(['name' => 'update permission']);
        Permission::create(['name' => 'delete permission']);

        // CRUD purchase permissions
        Permission::create(['name' => 'create purchase']);
        Permission::create(['name' => 'read purchase']);
        Permission::create(['name' => 'update purchase']);
        Permission::create(['name' => 'delete purchase']);

        // CRUD sale permissions
        Permission::create(['name' => 'create sale']);
        Permission::create(['name' => 'read sale']);
        Permission::create(['name' => 'update sale']);
        Permission::create(['name' => 'delete sale']);
    }
}
