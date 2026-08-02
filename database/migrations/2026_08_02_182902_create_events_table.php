<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['event', 'road_trip', 'vacation'])->default('event');
            $table->text('summary');
            $table->longText('description');

            // Trip Details
            $table->string('location');
            $table->string('pickup_location')->nullable(); // Essential for road trips
            $table->dateTime('start_date');
            $table->dateTime('end_date');

            // Capacity & Pricing
            $table->decimal('price', 10, 2);
            $table->integer('capacity')->unsigned();
            $table->integer('booked_slots')->default(0)->unsigned();

            // Status & Legal
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft');
            $table->longText('liability_waiver_text')->nullable(); // Custom consent text per event

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
