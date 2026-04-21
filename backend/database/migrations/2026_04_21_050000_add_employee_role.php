<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('roles')->insert([
            'name' => 'Employee',
            'description' => 'Employee account with access to Facility Requests and Work Orders',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        $requesterRole = DB::table('roles')->where('name', 'Requester')->first();
        $employeeRole = DB::table('roles')->where('name', 'Employee')->first();

        if ($requesterRole && $employeeRole) {
            DB::table('users')
                ->where('role_id', $employeeRole->id)
                ->update(['role_id' => $requesterRole->id]);
        }

        DB::table('roles')->where('name', 'Employee')->delete();
    }
};
