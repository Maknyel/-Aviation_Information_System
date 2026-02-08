<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            $table->timestamp('completed_at')->nullable()->after('assigned_at');
            $table->text('admin_remarks')->nullable()->after('completed_at');
        });

        // Staff skills table for smart assignment
        Schema::create('staff_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('skill'); // electrical, plumbing, IT, carpentry, general
            $table->enum('proficiency', ['beginner', 'intermediate', 'expert'])->default('intermediate');
            $table->timestamps();

            $table->unique(['user_id', 'skill']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_skills');
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['assigned_to', 'assigned_at', 'completed_at', 'admin_remarks']);
        });
    }
};
