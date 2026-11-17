<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Merge booking questions that were saved more than once on the same event.
     *
     * The oldest copy is kept. Answers given to a duplicate are moved onto the
     * kept question unless the booking already answered it, so no answers are
     * lost to the cascade delete.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $groups = DB::table('event_questions')
                ->select(['id', 'event_id', 'question_text'])
                ->orderBy('id')
                ->get()
                ->groupBy(fn (object $question): string => $question->event_id.'|'.mb_strtolower(trim($question->question_text)))
                ->filter(fn ($questions): bool => $questions->count() > 1);

            foreach ($groups as $questions) {
                $keptId = $questions->first()->id;
                $duplicateIds = $questions->skip(1)->pluck('id');

                $answeredBookingIds = DB::table('booking_responses')
                    ->where('event_question_id', $keptId)
                    ->pluck('booking_id');

                foreach ($duplicateIds as $duplicateId) {
                    $movable = DB::table('booking_responses')
                        ->where('event_question_id', $duplicateId)
                        ->whereNotIn('booking_id', $answeredBookingIds)
                        ->get(['id', 'booking_id'])
                        ->unique('booking_id');

                    DB::table('booking_responses')
                        ->whereIn('id', $movable->pluck('id'))
                        ->update(['event_question_id' => $keptId]);

                    $answeredBookingIds = $answeredBookingIds->merge($movable->pluck('booking_id'));
                }

                DB::table('booking_responses')->whereIn('event_question_id', $duplicateIds)->delete();
                DB::table('event_questions')->whereIn('id', $duplicateIds)->delete();
            }
        });
    }

    /**
     * Removed duplicates cannot be restored.
     */
    public function down(): void
    {
        //
    }
};
