<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\EventQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Seed events with media and questions.
     * Copies placeholder images from public/assets/images into storage.
     */
    public function run(): void
    {
        // Map public asset images to use as seeded media
        $publicImages = [
            'event-2.jpg',
            'event-3.jpg',
            'event-4.jpg',
            'event-5.jpg',
            'hero.jpg',
        ];

        $pastImages = ['past-3.jpg', 'past-4.jpg', 'past-5.jpg'];

        Storage::disk('public')->makeDirectory('events');

        $this->createUpcomingEvents($publicImages);
        $this->createCompletedEvents($pastImages);
    }

    /** @param  string[]  $images */
    private function createUpcomingEvents(array $images): void
    {
        $events = [
            [
                'title' => 'Naivasha Road Trip Weekend',
                'type' => 'road_trip',
                'summary' => 'A high-energy weekend escape to Hell\'s Gate and Lake Naivasha. Cycling, hiking, and lakeside vibes.',
                'location' => 'Naivasha',
                'pickup_location' => 'CBD Nairobi',
                'price' => 4500,
                'capacity' => 25,
                'days_offset' => 14,
                'questions' => [
                    ['question_text' => 'Dietary preference', 'type' => 'select', 'options' => ['No restriction', 'Vegetarian', 'Vegan', 'Halal']],
                    ['question_text' => 'Experience level', 'type' => 'select', 'options' => ['Beginner', 'Intermediate', 'Advanced']],
                ],
            ],
            [
                'title' => 'Diani Beach Escape',
                'type' => 'road_trip',
                'summary' => 'Three days of sun, sand, and ocean on one of Africa\'s most stunning beaches.',
                'location' => 'Diani',
                'pickup_location' => 'Westlands Nairobi',
                'price' => 12000,
                'capacity' => 20,
                'days_offset' => 21,
                'questions' => [
                    ['question_text' => 'Do you need airport pickup?', 'type' => 'select', 'options' => ['Yes', 'No']],
                    ['question_text' => 'T-shirt size', 'type' => 'select', 'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL']],
                ],
            ],
            [
                'title' => 'Mount Kenya Hike',
                'type' => 'event',
                'summary' => 'Summit Point Lenana on Kenya\'s highest peak. A true test of endurance and spirit.',
                'location' => 'Mount Kenya',
                'pickup_location' => 'Upper Hill Nairobi',
                'price' => 8500,
                'capacity' => 15,
                'days_offset' => 30,
                'questions' => [
                    ['question_text' => 'Have you hiked before?', 'type' => 'select', 'options' => ['Never', 'Once or twice', 'Regularly']],
                    ['question_text' => 'Any medical conditions we should know?', 'type' => 'text', 'is_required' => false],
                ],
            ],
            [
                'title' => 'Masai Mara Safari Day Trip',
                'type' => 'event',
                'summary' => 'Witness the Big Five in their natural habitat. A full-day game drive in the Mara.',
                'location' => 'Masai Mara',
                'pickup_location' => 'Karen Nairobi',
                'price' => 15000,
                'capacity' => 12,
                'days_offset' => 45,
                'questions' => [],
            ],
        ];

        foreach ($events as $index => $data) {
            $startDate = now()->addDays($data['days_offset']);
            $endDate = (clone $startDate)->addDays(rand(1, 3));

            $event = Event::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
                'type' => $data['type'],
                'summary' => $data['summary'],
                'description' => $this->generateDescription($data['title']),
                'location' => $data['location'],
                'pickup_location' => $data['pickup_location'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $data['price'],
                'capacity' => $data['capacity'],
                'booked_slots' => rand(2, 8),
                'status' => 'published',
                'liability_waiver_text' => 'By registering for this event, you acknowledge that participation involves physical activity and inherent risks. You agree to release Kivulini Adventures from any liability for injury, illness, or loss arising from participation. You confirm you are physically fit to participate and have adequate travel/medical insurance.',
            ]);

            // Attach an image from the public assets folder
            $imageFile = $images[$index % count($images)];
            $this->attachPublicImage($event, $imageFile, true, 0);

            // Add a second image for variety
            $second = $images[($index + 1) % count($images)];
            $this->attachPublicImage($event, $second, false, 1);

            // Create questions
            foreach ($data['questions'] as $qi => $q) {
                EventQuestion::create([
                    'event_id' => $event->id,
                    'question_text' => $q['question_text'],
                    'type' => $q['type'] ?? 'text',
                    'options' => $q['options'] ?? null,
                    'is_required' => $q['is_required'] ?? true,
                    'sort_order' => $qi,
                ]);
            }

            $this->command->info("Created event: {$event->title}");
        }
    }

    /** @param  string[]  $images */
    private function createCompletedEvents(array $images): void
    {
        $pastEvents = [
            ['title' => 'Nakuru National Park Trip', 'location' => 'Nakuru', 'days_ago' => 30],
            ['title' => 'Kisumu Lakeside Retreat', 'location' => 'Kisumu', 'days_ago' => 60],
        ];

        foreach ($pastEvents as $index => $data) {
            $startDate = now()->subDays($data['days_ago']);

            $event = Event::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
                'type' => 'road_trip',
                'summary' => 'An unforgettable trip that our crew absolutely loved. See you on the next one.',
                'description' => $this->generateDescription($data['title']),
                'location' => $data['location'],
                'pickup_location' => 'CBD Nairobi',
                'start_date' => $startDate,
                'end_date' => (clone $startDate)->addDays(2),
                'price' => 5000,
                'capacity' => 20,
                'booked_slots' => 18,
                'status' => 'completed',
                'liability_waiver_text' => null,
            ]);

            // Attach past images and mark as featured (showcased in gallery)
            foreach ($images as $pi => $imageFile) {
                $this->attachPublicImage($event, $imageFile, $pi === 0, $pi, true);
            }

            $this->command->info("Created completed event: {$event->title}");
        }
    }

    private function attachPublicImage(Event $event, string $filename, bool $isFeatured, int $sortOrder, bool $publishToGallery = false): void
    {
        $sourcePath = public_path("assets/images/{$filename}");

        if (! file_exists($sourcePath)) {
            return;
        }

        $storagePath = "events/{$event->id}/".Str::ulid().'.jpg';
        Storage::disk('public')->put($storagePath, file_get_contents($sourcePath));

        EventMedia::create([
            'event_id' => $event->id,
            'file_path' => $storagePath,
            'type' => 'image',
            'is_featured' => $isFeatured || $publishToGallery,
            'sort_order' => $sortOrder,
        ]);
    }

    private function generateDescription(string $title): string
    {
        return "<p>Join us for <strong>{$title}</strong> — one of Kivulini Adventures' most anticipated experiences of the year. Whether you're a seasoned explorer or just getting started, this trip is designed for bold individuals ready to create stories worth telling.</p><p>Our team handles all logistics so you can focus on what matters: the experience. Comfortable transport, handpicked accommodations, and a carefully planned itinerary await you.</p><p>Spots fill up fast. Lock in yours today and get ready to live.</p>";
    }
}
