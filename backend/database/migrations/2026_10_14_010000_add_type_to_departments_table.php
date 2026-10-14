<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            // Office, Student Org, Faculty or Employee
            $table->string('type')->default('Office')->after('code');
        });

        // The seeded institutes/programs are faculty groups; everything else stays an Office.
        DB::table('departments')->whereIn('code', ['INET', 'ICS', 'ILAS', 'AMT', 'FLOPS'])->update(['type' => 'Faculty']);
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
