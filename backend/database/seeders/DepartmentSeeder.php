<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            ['name' => 'Institute of Engineering and Technology', 'code' => 'INET', 'type' => 'Faculty', 'description' => 'Engineering and Technology Department'],
            ['name' => 'Institute of Computer Studies', 'code' => 'ICS', 'type' => 'Faculty', 'description' => 'Computer Studies Department'],
            ['name' => 'Institute of Liberal Arts and Sciences', 'code' => 'ILAS', 'type' => 'Faculty', 'description' => 'Liberal Arts and Sciences Department'],
            ['name' => 'General Services Office', 'code' => 'GSO', 'type' => 'Office', 'description' => 'General Services and Maintenance'],
            ['name' => 'Human Resources', 'code' => 'HR', 'type' => 'Office', 'description' => 'Human Resources Department'],
            ['name' => 'Administration', 'code' => 'ADMIN', 'type' => 'Office', 'description' => 'Administration Office'],
            ['name' => 'Flight Operations', 'code' => 'FLOPS', 'type' => 'Faculty', 'description' => 'Flight Operations Department'],
            ['name' => 'Aviation Maintenance', 'code' => 'AMT', 'type' => 'Faculty', 'description' => 'Aviation Maintenance Technology'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
