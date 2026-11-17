<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Builds the SEO <head> tags for public pages on the server.
 *
 * The tags are passed to the page as the "head" Inertia prop, printed directly
 * in app.blade.php (so crawlers see them without JavaScript or SSR) and kept in
 * sync by Inertia's serverHead option on client-side navigation. Every tag has
 * a stable data-inertia key so the client replaces it instead of duplicating it.
 */
final class Seo
{
    public const SITE_NAME = 'Kivulini Adventures';

    public const SITE_URL = 'https://kivuliniadventures.com';

    public const DEFAULT_IMAGE = self::SITE_URL.'/assets/images/og-home.jpg';

    public const LOGO = self::SITE_URL.'/assets/images/kivulini_logo.png';

    /** @var list<string> */
    public const SOCIAL_PROFILES = [
        'https://www.instagram.com/_kivulini.adventures/',
        'https://www.tiktok.com/@kivuliniadventures',
    ];

    private string $type = 'website';

    private ?string $canonical = null;

    private string $image = self::DEFAULT_IMAGE;

    private ?string $keywords = null;

    private bool $indexable = true;

    /** @var list<array<string, mixed>> */
    private array $schemas = [];

    private function __construct(private string $title, private string $description) {}

    public static function make(string $title, string $description): self
    {
        return new self($title, $description);
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function canonical(string $pathOrUrl): self
    {
        $this->canonical = Str::startsWith($pathOrUrl, 'http')
            ? $pathOrUrl
            : self::SITE_URL.'/'.ltrim($pathOrUrl, '/');

        return $this;
    }

    public function image(?string $url): self
    {
        if (filled($url)) {
            $this->image = Str::startsWith($url, 'http') ? $url : self::SITE_URL.'/'.ltrim($url, '/');
        }

        return $this;
    }

    public function keywords(string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function noIndex(): self
    {
        $this->indexable = false;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public function schema(array $schema): self
    {
        $this->schemas[] = ['@context' => 'https://schema.org', ...$schema];

        return $this;
    }

    /**
     * Site-wide identity: tells Google the site name and brand details.
     */
    public function withSiteIdentity(): self
    {
        return $this
            ->schema([
                '@type' => 'WebSite',
                'name' => self::SITE_NAME,
                'alternateName' => ['Kivulini', 'kivuliniadventures.com'],
                'url' => self::SITE_URL.'/',
            ])
            ->schema([
                '@type' => 'TravelAgency',
                'name' => self::SITE_NAME,
                'url' => self::SITE_URL.'/',
                'logo' => self::LOGO,
                'image' => self::DEFAULT_IMAGE,
                'description' => 'Kenyan adventure travel brand running curated road trips, hikes and getaway vacations.',
                'email' => 'info@kivuliniadventures.com',
                'telephone' => '+254725038004',
                'areaServed' => 'Kenya',
                'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'KE'],
                'sameAs' => self::SOCIAL_PROFILES,
            ]);
    }

    public function fullTitle(): string
    {
        return Str::contains($this->title, self::SITE_NAME)
            ? $this->title
            : $this->title.' | '.self::SITE_NAME;
    }

    /**
     * @return list<string>
     */
    public function toHead(): array
    {
        $title = $this->fullTitle();
        $description = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($this->description)) ?? ''), 160);

        $tags = [
            $this->tag('title', 'title', [], e($title)),
            $this->tag('description', 'meta', ['name' => 'description', 'content' => $description]),
            $this->tag('robots', 'meta', ['name' => 'robots', 'content' => $this->indexable ? 'index, follow, max-image-preview:large' : 'noindex, nofollow']),
            $this->keywords ? $this->tag('keywords', 'meta', ['name' => 'keywords', 'content' => $this->keywords]) : null,
            $this->canonical ? $this->tag('canonical', 'link', ['rel' => 'canonical', 'href' => $this->canonical]) : null,

            $this->tag('og:site_name', 'meta', ['property' => 'og:site_name', 'content' => self::SITE_NAME]),
            $this->tag('og:type', 'meta', ['property' => 'og:type', 'content' => $this->type]),
            $this->tag('og:title', 'meta', ['property' => 'og:title', 'content' => $title]),
            $this->tag('og:description', 'meta', ['property' => 'og:description', 'content' => $description]),
            $this->canonical ? $this->tag('og:url', 'meta', ['property' => 'og:url', 'content' => $this->canonical]) : null,
            $this->tag('og:image', 'meta', ['property' => 'og:image', 'content' => $this->image]),
            $this->tag('og:locale', 'meta', ['property' => 'og:locale', 'content' => 'en_KE']),

            $this->tag('twitter:card', 'meta', ['name' => 'twitter:card', 'content' => 'summary_large_image']),
            $this->tag('twitter:title', 'meta', ['name' => 'twitter:title', 'content' => $title]),
            $this->tag('twitter:description', 'meta', ['name' => 'twitter:description', 'content' => $description]),
            $this->tag('twitter:image', 'meta', ['name' => 'twitter:image', 'content' => $this->image]),
        ];

        foreach ($this->schemas as $index => $schema) {
            $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
            $tags[] = $this->tag("schema-{$index}", 'script', ['type' => 'application/ld+json'], (string) $json);
        }

        return array_values(array_filter($tags));
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function tag(string $key, string $element, array $attributes, ?string $content = null): string
    {
        $html = '<'.$element.' data-inertia="seo-'.e($key).'"';

        foreach ($attributes as $name => $value) {
            $html .= ' '.$name.'="'.e($value).'"';
        }

        return $content === null && $element !== 'script' && $element !== 'title'
            ? $html.'>'
            : $html.'>'.$content.'</'.$element.'>';
    }
}
