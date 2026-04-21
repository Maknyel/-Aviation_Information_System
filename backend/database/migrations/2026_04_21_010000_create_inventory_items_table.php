<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('category', 100)->default('utility'); // furniture, av_equipment, utility
            $table->unsignedInteger('total_quantity')->default(0);
            $table->string('unit', 50)->default('pcs'); // pcs, sets, units
            $table->enum('condition', ['good', 'fair', 'poor'])->default('good');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
};
