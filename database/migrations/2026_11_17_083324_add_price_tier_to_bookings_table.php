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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('event_price_tier_id')->nullable()->after('event_id')->constrained()->nullOnDelete();
            $table->string('tier_name', 50)->nullable()->after('quantity');
            $table->decimal('unit_price', 10, 2)->nullable()->after('tier_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_price_tier_id');
            $table->dropColumn(['tier_name', 'unit_price']);
        });
    }
};
