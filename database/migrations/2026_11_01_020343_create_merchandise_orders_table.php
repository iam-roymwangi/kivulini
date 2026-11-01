<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchandise_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_reference')->unique();
            $table->foreignId('merchandise_id')->constrained('merchandise')->restrictOnDelete();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('payment_status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->index(['payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchandise_orders');
    }
};
