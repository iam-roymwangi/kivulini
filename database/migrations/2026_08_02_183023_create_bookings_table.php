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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique(); // e.g., BK-2026-X9Y2
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Primary Contact Details (Direct Booking)
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');

            // Ticket Count & Financials
            $table->integer('quantity')->default(1)->unsigned();
            $table->decimal('total_price', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_reference')->nullable(); // M-Pesa / Card Transaction ID

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
