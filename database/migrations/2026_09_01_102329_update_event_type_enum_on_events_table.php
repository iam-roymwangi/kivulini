<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NEW_TYPES = ['cultural_heritage', 'wildlife_safari', 'food_music', 'road_trip', 'hiking', 'vacation'];

    private const OLD_TYPES = ['event', 'road_trip', 'vacation'];

    public function up(): void
    {
        // Remap any legacy 'event' values before changing the constraint
        DB::table('events')->where('type', 'event')->update(['type' => 'cultural_heritage']);

        if (DB::getDriverName() === 'mysql') {
            $types = implode("','", self::NEW_TYPES);
            DB::statement("ALTER TABLE `events` MODIFY COLUMN `type` ENUM('{$types}') NOT NULL DEFAULT 'cultural_heritage'");
        }
        // SQLite: the original create_events_table migration already uses the new enum
        // values, so no structural change is needed. Table recreation corrupts SQLite FK
        // caches and is intentionally avoided.
    }

    public function down(): void
    {
        DB::table('events')->whereNotIn('type', self::OLD_TYPES)->update(['type' => 'event']);

        if (DB::getDriverName() === 'mysql') {
            $types = implode("','", self::OLD_TYPES);
            DB::statement("ALTER TABLE `events` MODIFY COLUMN `type` ENUM('{$types}') NOT NULL DEFAULT 'event'");
        }
    }
};
