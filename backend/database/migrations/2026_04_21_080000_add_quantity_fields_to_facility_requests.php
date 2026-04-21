<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->unsignedInteger('chair_qty')->default(0)->after('chair');
            $table->unsignedInteger('podium_qty')->default(0)->after('podium');
            $table->unsignedInteger('tent_qty')->default(0)->after('tent');
            $table->unsignedInteger('tables_qty')->default(0)->after('tables');
            $table->unsignedInteger('booths_qty')->default(0)->after('booths');
            $table->unsignedInteger('sound_system_qty')->default(0)->after('sound_system');
            $table->unsignedInteger('extension_qty')->default(0)->after('extension');
            $table->unsignedInteger('microphones_qty')->default(0)->after('microphones');
            $table->unsignedInteger('skirting_qty')->default(0)->after('skirting');
            $table->unsignedInteger('flags_qty')->default(0)->after('flags');
        });
    }

    public function down(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->dropColumn([
                'chair_qty','podium_qty','tent_qty','tables_qty','booths_qty',
                'sound_system_qty','extension_qty','microphones_qty','skirting_qty','flags_qty',
            ]);
        });
    }
};
