<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // INET, ICS, ILAS, GSO, etc.
            $table->string('code')->unique(); // Short code
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Add department_id to facility_requests
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained('departments')->onDelete('set null');
        });

        // Add department_id to work_orders
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained('departments')->onDelete('set null');
        });

        // Add department_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('role_id')->constrained('departments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
        Schema::dropIfExists('departments');
    }
};
