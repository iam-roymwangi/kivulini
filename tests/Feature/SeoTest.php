<?php

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\EventPriceTier;
use App\Models\Merchandise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

/**
 * @return list<array<string, mixed>>
 */
function jsonLdBlocks(string $html): array
{
    preg_match_all('#<script data-inertia="seo-schema-\d+" type="application/ld\+json">(.*?)</script>#s', $html, $matches);

    return array_map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
}

test('home page html includes description, canonical and site identity without javascript', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('<title data-inertia="seo-title">Kivulini Adventures | Road Trips, Hikes &amp; Getaways in Kenya</title>')
        ->toContain('<meta data-inertia="seo-description" name="description" content="Kivulini Adventures curates road trips')
        ->toContain('<link data-inertia="seo-canonical" rel="canonical" href="https://kivuliniadventures.com/">')
        ->toContain('content="https://kivuliniadventures.com/assets/images/og-home.jpg"')
        ->and(substr_count($html, '<title data-inertia="seo-title">'))->toBe(1)
        ->and(substr_count($html, '<title>'))->toBe(0)
        ->and(substr_count($html, ' name="description"'))->toBe(1);

    $schemas = collect(jsonLdBlocks($html))->keyBy('@type');

    expect($schemas['WebSite']['name'])->toBe('Kivulini Adventures')
        ->and($schemas['TravelAgency']['sameAs'])->toContain('https://www.tiktok.com/@kivuliniadventures');
});

test('static public pages have their own title and description', function (string $path, string $title) {
    $html = $this->get($path)->assertOk()->getContent();

    expect($html)
        ->toContain("<title data-inertia=\"seo-title\">{$title} | Kivulini Adventures</title>")
        ->toContain('name="description"')
        ->toContain('href="https://kivuliniadventures.com'.$path.'"');
})->with([
    ['/events', 'Upcoming Trips &amp; Events'],
    ['/about', 'About Us'],
    ['/contact', 'Contact Us'],
    ['/gallery', 'Past Trips Photo Gallery'],
    ['/merchandise', 'Merchandise – Wear the Adventure'],
    ['/terms', 'Terms and Conditions'],
    ['/privacy', 'Privacy Policy'],
]);

test('event page html includes event details and Event structured data', function () {
    $event = Event::factory()->create([
        'title' => 'Mt. Longonot Sunrise Hike',
        'summary' => 'Catch sunrise over the Rift Valley from the crater rim.',
        'location' => 'Naivasha',
        'capacity' => 20,
        'booked_slots' => 0,
    ]);
    EventMedia::factory()->create(['event_id' => $event->id, 'file_path' => 'events/longonot.webp', 'is_featured' => true]);
    EventPriceTier::factory()->for($event)->create(['name' => 'VIP', 'price' => 5000]);

    $html = $this->get(route('events.show', $event->slug))->assertOk()->getContent();

    expect($html)
        ->toContain('Mt. Longonot Sunrise Hike | Kivulini Adventures</title>')
        ->toContain('content="Catch sunrise over the Rift Valley from the crater rim."')
        ->toContain('content="https://kivuliniadventures.com/storage/events/longonot.webp"')
        ->toContain('content="index, follow, max-image-preview:large"');

    $schema = collect(jsonLdBlocks($html))->firstWhere('@type', 'Event');

    expect($schema['name'])->toBe('Mt. Longonot Sunrise Hike')
        ->and($schema['location']['name'])->toBe('Naivasha')
        ->and($schema['offers'][0])->toMatchArray(['name' => 'VIP', 'price' => '5000.00', 'priceCurrency' => 'KES', 'availability' => 'https://schema.org/InStock']);
});

test('draft event pages are hidden from search engines', function () {
    $event = Event::factory()->create(['status' => 'draft']);

    expect($this->get(route('events.show', $event->slug))->getContent())
        ->toContain('content="noindex, nofollow"');
});

test('seo tags are sent as the head prop for client-side navigation', function () {
    $this->get('/about')->assertInertia(fn ($page) => $page
        ->where('head.0', '<title data-inertia="seo-title">About Us | Kivulini Adventures</title>'));
});

test('admin pages fall back to the default title', function () {
    $this->actingAs(User::factory()->create());

    expect($this->get(route('admin.bookings.index'))->getContent())
        ->toContain('<title>Kivulini Adventures</title>')
        ->not->toContain('seo-description');
});

test('sitemap lists public pages, trips and products but not drafts', function () {
    $published = Event::factory()->create(['status' => 'published']);
    $completed = Event::factory()->create(['status' => 'completed']);
    $draft = Event::factory()->create(['status' => 'draft']);
    $merch = Merchandise::factory()->create(['status' => 'published']);

    $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    $xml = simplexml_load_string($response->getContent());
    $locations = collect();

    foreach ($xml->url as $url) {
        $locations->push((string) $url->loc);
    }

    expect($locations)
        ->toContain('https://kivuliniadventures.com/')
        ->toContain('https://kivuliniadventures.com/about')
        ->toContain("https://kivuliniadventures.com/events/{$published->slug}")
        ->toContain("https://kivuliniadventures.com/events/{$completed->slug}")
        ->toContain("https://kivuliniadventures.com/merchandise/{$merch->id}")
        ->not->toContain("https://kivuliniadventures.com/events/{$draft->slug}");
});
