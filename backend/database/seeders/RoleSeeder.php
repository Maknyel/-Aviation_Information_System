<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'Administrator with full system access',
            ],
            [
                'name' => 'Staff',
                'description' => 'Staff member with limited access',
            ],
            [
                'name' => 'Student',
                'description' => 'Student with basic access',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
