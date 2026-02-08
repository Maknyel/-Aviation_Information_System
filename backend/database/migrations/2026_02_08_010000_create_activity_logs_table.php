<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // created, updated, deleted, status_changed, login, logout
            $table->string('model_type')->nullable(); // FacilityRequest, WorkOrder, User, etc.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
            $table->index('action');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};
