<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->morphs('payable');
            $table->string('phone', 15);
            $table->unsignedInteger('amount');
            $table->string('account_reference', 12);
            $table->string('description', 20);
            $table->string('merchant_request_id')->nullable();
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->string('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->string('mpesa_receipt_number')->nullable()->unique();
            $table->json('callback_payload')->nullable();
            $table->timestamp('queried_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
