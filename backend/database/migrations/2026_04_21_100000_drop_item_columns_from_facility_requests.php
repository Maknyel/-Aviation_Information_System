<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->dropColumn([
                'chair','chair_qty','podium','podium_qty','tent','tent_qty',
                'tables','tables_qty','booths','booths_qty',
                'sound_system','sound_system_qty','extension','extension_qty',
                'microphones','microphones_qty','skirting','skirting_qty',
                'flags','flags_qty','others','others_description',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->boolean('chair')->default(false);
            $table->unsignedInteger('chair_qty')->default(0);
            $table->boolean('podium')->default(false);
            $table->unsignedInteger('podium_qty')->default(0);
            $table->boolean('tent')->default(false);
            $table->unsignedInteger('tent_qty')->default(0);
            $table->boolean('tables')->default(false);
            $table->unsignedInteger('tables_qty')->default(0);
            $table->boolean('booths')->default(false);
            $table->unsignedInteger('booths_qty')->default(0);
            $table->boolean('sound_system')->default(false);
            $table->unsignedInteger('sound_system_qty')->default(0);
            $table->boolean('extension')->default(false);
            $table->unsignedInteger('extension_qty')->default(0);
            $table->boolean('microphones')->default(false);
            $table->unsignedInteger('microphones_qty')->default(0);
            $table->boolean('skirting')->default(false);
            $table->unsignedInteger('skirting_qty')->default(0);
            $table->boolean('flags')->default(false);
            $table->unsignedInteger('flags_qty')->default(0);
            $table->boolean('others')->default(false);
            $table->text('others_description')->nullable();
        });
    }
};
