<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->unsignedInteger('quantity_assigned');
            $table->string('assigned_location', 255);
            $table->string('reference_type', 50)->nullable(); // facility_request, work_order, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at');
            $table->timestamp('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable(); // null = still deployed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['inventory_item_id', 'returned_at']);
            $table->index('reference_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_assignments');
    }
};
