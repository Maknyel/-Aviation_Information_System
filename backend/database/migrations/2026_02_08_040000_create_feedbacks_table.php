<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('request_type'); // facility_request, work_order
            $table->unsignedBigInteger('request_id');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['request_type', 'request_id', 'user_id']);
            $table->index(['request_type', 'request_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
};
