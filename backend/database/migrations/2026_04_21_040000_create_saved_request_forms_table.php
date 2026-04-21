<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('saved_request_forms', function (Blueprint $table) {
            $table->id();
            $table->enum('request_type', ['facility_request', 'work_order']);
            $table->unsignedBigInteger('request_id');
            $table->foreignId('saved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['request_type', 'request_id']);
            $table->index(['request_type', 'request_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('saved_request_forms');
    }
};
