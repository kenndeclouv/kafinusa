<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesNames = ['Andika', 'Puji', 'Jeheri', 'Edi', 'Wahyudi', 'Soleh'];
        
        // Start from a high number to avoid conflict with existing dummies if any
        $startIndex = 100;

        foreach ($salesNames as $index => $name) {
            $email = strtolower($name) . '@example.com';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                ]
            );
            
            if (!$user->hasRole('sales')) {
                $user->assignRole('sales');
            }

            Employee::firstOrCreate(
                ['user_id' => $user->id], // Use user_id to find existing employee for this user
                [
                    'employee_id_number' => 'EMP-' . str_pad($startIndex + $index, 3, '0', STR_PAD_LEFT),
                    'name' => $name,
                    'phone_number' => '08123456789' . $index,
                    'position' => 'Sales'
                ]
            );
        }
    }
}
