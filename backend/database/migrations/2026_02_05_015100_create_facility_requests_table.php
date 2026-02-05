<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('venue_requested');
            $table->string('location_room_number')->nullable();
            $table->string('title_of_event');
            $table->string('time_of_event');
            $table->date('date_of_event');

            // Equipment/Facilities checkboxes
            $table->boolean('chair')->default(false);
            $table->boolean('podium')->default(false);
            $table->boolean('tent')->default(false);
            $table->boolean('tables')->default(false);
            $table->boolean('booths')->default(false);
            $table->boolean('sound_system')->default(false);
            $table->boolean('extension')->default(false);
            $table->boolean('microphones')->default(false);
            $table->boolean('skirting')->default(false);
            $table->boolean('flags')->default(false);
            $table->boolean('others')->default(false);
            $table->text('others_description')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'canceled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facility_requests');
    }
};
