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
            $table->string('follow_up_status', 20)->default('new')->after('terms_accepted_at')->index();
            $table->text('follow_up_notes')->nullable()->after('follow_up_status');
            $table->timestamp('followed_up_at')->nullable()->after('follow_up_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['follow_up_status']);
            $table->dropColumn(['follow_up_status', 'follow_up_notes', 'followed_up_at']);
        });
    }
};
