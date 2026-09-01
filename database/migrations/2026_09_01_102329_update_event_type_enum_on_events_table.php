<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SQLite doesn't support ALTER COLUMN so we recreate the table manually.
     * Steps: rename original → create new with updated CHECK → copy data → drop old.
     */
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('ALTER TABLE "events" RENAME TO "__old__events"');

        DB::statement('
            CREATE TABLE "events" (
                "id"                    integer primary key autoincrement not null,
                "title"                 varchar not null,
                "slug"                  varchar not null,
                "type"                  varchar check("type" in (\'cultural_heritage\',\'wildlife_safari\',\'food_music\',\'road_trip\',\'hiking\',\'vacation\')) not null default \'cultural_heritage\',
                "summary"               text not null,
                "description"           text not null,
                "location"              varchar not null,
                "pickup_location"       varchar,
                "start_date"            datetime not null,
                "end_date"              datetime not null,
                "price"                 numeric not null,
                "capacity"              integer not null,
                "booked_slots"          integer not null default \'0\',
                "status"                varchar check("status" in (\'draft\',\'published\',\'completed\',\'cancelled\')) not null default \'draft\',
                "liability_waiver_text" text,
                "created_at"            datetime,
                "updated_at"            datetime,
                "deleted_at"            datetime
            )
        ');

        // Copy rows, remapping old → new type values
        DB::statement("
            INSERT INTO \"events\"
            SELECT
                \"id\", \"title\", \"slug\",
                CASE \"type\"
                    WHEN 'event'     THEN 'cultural_heritage'
                    WHEN 'road_trip' THEN 'road_trip'
                    WHEN 'vacation'  THEN 'vacation'
                    ELSE 'cultural_heritage'
                END,
                \"summary\", \"description\", \"location\", \"pickup_location\",
                \"start_date\", \"end_date\", \"price\", \"capacity\", \"booked_slots\",
                \"status\", \"liability_waiver_text\", \"created_at\", \"updated_at\", \"deleted_at\"
            FROM \"__old__events\"
        ");

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS "events_slug_unique" ON "events" ("slug")');

        DB::statement('DROP TABLE "__old__events"');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('ALTER TABLE "events" RENAME TO "__old__events"');
        DB::statement('
            CREATE TABLE "events" (
                "id"                    integer primary key autoincrement not null,
                "title"                 varchar not null,
                "slug"                  varchar not null,
                "type"                  varchar check("type" in (\'event\',\'road_trip\',\'vacation\')) not null default \'event\',
                "summary"               text not null,
                "description"           text not null,
                "location"              varchar not null,
                "pickup_location"       varchar,
                "start_date"            datetime not null,
                "end_date"              datetime not null,
                "price"                 numeric not null,
                "capacity"              integer not null,
                "booked_slots"          integer not null default \'0\',
                "status"                varchar check("status" in (\'draft\',\'published\',\'completed\',\'cancelled\')) not null default \'draft\',
                "liability_waiver_text" text,
                "created_at"            datetime,
                "updated_at"            datetime,
                "deleted_at"            datetime
            )
        ');

        DB::statement("
            INSERT INTO \"events\"
            SELECT
                \"id\", \"title\", \"slug\",
                CASE \"type\"
                    WHEN 'road_trip' THEN 'road_trip'
                    WHEN 'vacation'  THEN 'vacation'
                    ELSE 'event'
                END,
                \"summary\", \"description\", \"location\", \"pickup_location\",
                \"start_date\", \"end_date\", \"price\", \"capacity\", \"booked_slots\",
                \"status\", \"liability_waiver_text\", \"created_at\", \"updated_at\", \"deleted_at\"
            FROM \"__old__events\"
        ");

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS "events_slug_unique" ON "events" ("slug")');

        DB::statement('DROP TABLE "__old__events"');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
