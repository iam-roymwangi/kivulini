<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach ($staticPages as $page)
    <url>
        <loc>{{ $page['url'] }}</loc>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </url>
    @endforeach

    @foreach ($events as $event)
    <url>
        <loc>{{ route('events.show', $event->slug) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <lastmod>{{ $event->updated_at->toAtomString() }}</lastmod>
    </url>
    @endforeach

</urlset>
