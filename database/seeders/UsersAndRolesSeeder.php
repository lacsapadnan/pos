<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UsersAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Assign 'admin' role to the admin user
        $adminRole = Role::create(['name' => 'admin']);
        $adminUser->roles()->attach($adminRole);

        // Create kasir user
        $kasirUser = User::create([
            'name' => 'Kasir User',
            'email' => 'kasir@example.com',
            'password' => bcrypt('password'),
        ]);

        // Assign 'kasir' role to the kasir user
        $kasirRole = Role::create(['name' => 'kasir']);
        $kasirUser->roles()->attach($kasirRole);

        // Create gudang user
        $gudangUser = User::create([
            'name' => 'Gudang User',
            'email' => 'gudang@example.com',
            'password' => bcrypt('password'),
        ]);

        // Assign 'gudang' role to the gudang user
        $gudangRole = Role::create(['name' => 'gudang']);
        $gudangUser->roles()->attach($gudangRole);
    }
}
