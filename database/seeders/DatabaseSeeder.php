<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            CustomerCategorySeeder::class,
            ItemCategorySeeder::class,
            MarketSeeder::class,
            CustomerSeeder::class,
            ItemSeeder::class,
            EmployeeSeeder::class,
            SalesScheduleSeeder::class,
        ]);

        $superadmin = User::firstOrCreate(
            ['email' => 'kenndeclouv@gmail.com'],
            [
                'name' => 'Kennde Clouv',
                'password' => Hash::make('kenndeclouv'),
            ]
        );
        $superadmin->assignRole('superadmin');
    }
}
