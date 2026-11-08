<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

/**
 * Reusable SEO head component for all public-facing pages.
 *
 * Usage:
 *   <SEOHead
 *     title="Page Title"
 *     description="Page description."
 *     keywords="keyword1, keyword2"
 *     canonical="https://kivuliniadventures.com/page"
 *     ogType="website"
 *     ogImage="https://…/image.webp"
 *     twitterCard="summary_large_image"
 *   />
 */
interface Props {
    /** The page <title> — appended with " - Kivulini Adventures" by app.ts */
    title: string;
    /** Meta description (< 160 chars recommended) */
    description: string;
    /** Comma-separated keywords */
    keywords?: string;
    /** Canonical URL for this page */
    canonical?: string;
    /** og:type — defaults to "website" */
    ogType?: 'website' | 'article' | 'product';
    /** og:url override — defaults to canonical */
    ogUrl?: string;
    /** og:image absolute URL */
    ogImage?: string;
    /** twitter:card — defaults to "summary_large_image" */
    twitterCard?: 'summary' | 'summary_large_image';
    /** twitter:image override — defaults to ogImage */
    twitterImage?: string;
    /** Structured data JSON-LD string (pre-serialised) */
    jsonLd?: string;
}

const props = withDefaults(defineProps<Props>(), {
    keywords: undefined,
    canonical: undefined,
    ogType: 'website',
    ogUrl: undefined,
    ogImage: 'https://kivuliniadventures.com/assets/images/og-home.webp',
    twitterCard: 'summary_large_image',
    twitterImage: undefined,
    jsonLd: undefined,
});

const resolvedOgUrl = props.ogUrl ?? props.canonical;
const resolvedTwitterImage = props.twitterImage ?? props.ogImage;
</script>

<template>
    <Head>
        <title>{{ title }}</title>
        <meta name="description" :content="description" />
        <meta v-if="keywords" name="keywords" :content="keywords" />
        <link v-if="canonical" rel="canonical" :href="canonical" />

        <!-- Open Graph -->
        <meta property="og:site_name" content="Kivulini Adventures" />
        <meta property="og:type" :content="ogType" />
        <meta property="og:title" :content="`${title} – Kivulini Adventures`" />
        <meta property="og:description" :content="description" />
        <meta v-if="resolvedOgUrl" property="og:url" :content="resolvedOgUrl" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
        <meta v-if="ogImage" property="og:image:width" content="1200" />
        <meta v-if="ogImage" property="og:image:height" content="630" />
        <meta v-if="ogImage" property="og:image:alt" :content="title" />
        <meta property="og:locale" content="en_KE" />

        <!-- Twitter / X Card -->
        <meta name="twitter:card" :content="twitterCard" />
        <meta name="twitter:site" content="@KivuliniAdv" />
        <meta name="twitter:title" :content="`${title} – Kivulini Adventures`" />
        <meta name="twitter:description" :content="description" />
        <meta v-if="resolvedTwitterImage" name="twitter:image" :content="resolvedTwitterImage" />
        <meta v-if="resolvedTwitterImage" name="twitter:image:alt" :content="title" />

        <!-- JSON-LD structured data -->
        <!-- eslint-disable-next-line vue/no-v-html -->
        <component v-if="jsonLd" :is="'script'" type="application/ld+json" v-html="jsonLd" />
    </Head>
</template>
