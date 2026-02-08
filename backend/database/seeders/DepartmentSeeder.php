<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            ['name' => 'Institute of Engineering and Technology', 'code' => 'INET', 'description' => 'Engineering and Technology Department'],
            ['name' => 'Institute of Computer Studies', 'code' => 'ICS', 'description' => 'Computer Studies Department'],
            ['name' => 'Institute of Liberal Arts and Sciences', 'code' => 'ILAS', 'description' => 'Liberal Arts and Sciences Department'],
            ['name' => 'General Services Office', 'code' => 'GSO', 'description' => 'General Services and Maintenance'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Human Resources Department'],
            ['name' => 'Administration', 'code' => 'ADMIN', 'description' => 'Administration Office'],
            ['name' => 'Flight Operations', 'code' => 'FLOPS', 'description' => 'Flight Operations Department'],
            ['name' => 'Aviation Maintenance', 'code' => 'AMT', 'description' => 'Aviation Maintenance Technology'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
