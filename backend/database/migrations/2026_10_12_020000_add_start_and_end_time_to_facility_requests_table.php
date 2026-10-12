<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->string('start_time')->nullable()->after('time_of_event');
            $table->string('end_time')->nullable()->after('start_time');
        });

        // Existing requests only had a single time, which becomes the start time.
        DB::table('facility_requests')->update(['start_time' => DB::raw('time_of_event')]);
    }

    public function down()
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
