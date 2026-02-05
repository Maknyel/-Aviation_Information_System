<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
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
        $roles = Role::all();

        foreach ($roles as $role) {
            User::create([
                'name' => $role->name . ' User',
                'email' => strtolower($role->name) . '@aviation.com',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'email_verified_at' => now(),
            ]);
        }
    }
}
