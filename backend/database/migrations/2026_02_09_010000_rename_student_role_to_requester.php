<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'Student')
            ->update([
                'name' => 'Requester',
                'description' => 'Requester with basic access',
            ]);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'Requester')
            ->update([
                'name' => 'Student',
                'description' => 'Student with basic access',
            ]);
    }
};
