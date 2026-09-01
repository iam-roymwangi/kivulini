<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NEW_TYPES = ['cultural_heritage', 'wildlife_safari', 'food_music', 'road_trip', 'hiking', 'vacation'];

    private const OLD_TYPES = ['event', 'road_trip', 'vacation'];

    public function up(): void
    {
        // Remap old values before changing the constraint
        DB::table('events')->where('type', 'event')->update(['type' => 'cultural_heritage']);

        if (DB::getDriverName() === 'mysql') {
            $types = implode("','", self::NEW_TYPES);
            DB::statement("ALTER TABLE `events` MODIFY COLUMN `type` ENUM('{$types}') NOT NULL DEFAULT 'cultural_heritage'");
        } elseif (DB::getDatabaseName() !== ':memory:') {
            // File-based SQLite: recreate table to change the CHECK constraint
            $this->recreateSqlite(self::NEW_TYPES, 'cultural_heritage');
        }
        // In-memory SQLite (tests): table was just created with correct schema
        // via RefreshDatabase, so no structural change is needed.
    }

    public function down(): void
    {
        DB::table('events')->whereNotIn('type', self::OLD_TYPES)->update(['type' => 'event']);

        if (DB::getDriverName() === 'mysql') {
            $types = implode("','", self::OLD_TYPES);
            DB::statement("ALTER TABLE `events` MODIFY COLUMN `type` ENUM('{$types}') NOT NULL DEFAULT 'event'");
        } elseif (DB::getDatabaseName() !== ':memory:') {
            $this->recreateSqlite(self::OLD_TYPES, 'event');
        }
    }

    /**
     * SQLite doesn't support ALTER COLUMN, so we recreate the table.
     * Only called for file-based SQLite (not :memory:).
     *
     * @param  string[]  $types
     */
    private function recreateSqlite(array $types, string $default): void
    {
        $check = implode("','", $types);

        Schema::disableForeignKeyConstraints();

        DB::statement('ALTER TABLE "events" RENAME TO "__old__events"');

        DB::statement("
            CREATE TABLE \"events\" (
                \"id\"                    integer primary key autoincrement not null,
                \"title\"                 varchar not null,
                \"slug\"                  varchar not null,
                \"type\"                  varchar check(\"type\" in ('{$check}')) not null default '{$default}',
                \"summary\"               text not null,
                \"description\"           text not null,
                \"location\"              varchar not null,
                \"pickup_location\"       varchar,
                \"start_date\"            datetime not null,
                \"end_date\"              datetime not null,
                \"price\"                 numeric not null,
                \"capacity\"              integer not null,
                \"booked_slots\"          integer not null default '0',
                \"status\"                varchar check(\"status\" in ('draft','published','completed','cancelled')) not null default 'draft',
                \"liability_waiver_text\" text,
                \"created_at\"            datetime,
                \"updated_at\"            datetime,
                \"deleted_at\"            datetime
            )
        ");

        DB::statement('
            INSERT INTO "events"
            SELECT "id","title","slug","type","summary","description","location","pickup_location",
                   "start_date","end_date","price","capacity","booked_slots","status",
                   "liability_waiver_text","created_at","updated_at","deleted_at"
            FROM "__old__events"
        ');

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS "events_slug_unique" ON "events" ("slug")');

        DB::statement('DROP TABLE "__old__events"');

        Schema::enableForeignKeyConstraints();
    }
};
