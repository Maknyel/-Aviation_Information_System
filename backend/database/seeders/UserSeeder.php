<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = ['Admin', 'HR', 'Employees', 'Staff', 'Students'];

        foreach ($roles as $role) {
            User::create([
                'name' => $role . ' User',
                'email' => strtolower($role) . '@aviation.com',
                'password' => Hash::make('password'),
                'role' => $role,
                'email_verified_at' => now(),
            ]);
        }
    }
}
