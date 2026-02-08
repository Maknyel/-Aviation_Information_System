<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->string('request_type'); // facility_request, work_order
            $table->unsignedBigInteger('request_id');
            $table->unsignedInteger('step_order'); // 1, 2, 3...
            $table->string('approver_role'); // Staff, Admin, HR
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(['request_type', 'request_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_steps');
    }
};
