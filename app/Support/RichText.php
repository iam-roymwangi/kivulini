<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

final class RichText
{
    /**
     * Tags the rich text editor is allowed to produce.
     *
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h2', 'h3', 'h4',
        'ul', 'ol', 'li', 'blockquote', 'a', 'div', 'span',
    ];

    /**
     * Tags whose content is dropped entirely rather than unwrapped.
     *
     * @var list<string>
     */
    private const DROPPED_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'noscript'];

    /**
     * Sanitize editor HTML, keeping formatting tags and safe links only.
     * Plain text (no tags) is converted into paragraphs and line breaks.
     */
    public static function sanitize(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        if ($html === strip_tags($html)) {
            return self::fromPlainText($html);
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="rich-text-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('rich-text-root');

        if ($root === null) {
            return e(strip_tags($html));
        }

        self::cleanChildren($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private static function fromPlainText(string $text): string
    {
        $paragraphs = preg_split('/\R{2,}/', $text) ?: [];

        return collect($paragraphs)
            ->map(fn (string $paragraph): string => trim($paragraph))
            ->filter()
            ->map(fn (string $paragraph): string => '<p>'.preg_replace('/\R/', '<br>', e($paragraph)).'</p>')
            ->implode('');
    }

    private static function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROPPED_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            self::cleanChildren($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child, $tag);
        }
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $href = $element->getAttribute('href');

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttribute($attribute->nodeName);
        }

        if ($tag !== 'a') {
            return;
        }

        if (preg_match('/^(https?:|mailto:|tel:)/i', trim($href)) === 1) {
            $element->setAttribute('href', trim($href));
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }
}
