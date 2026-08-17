<?php

namespace App\Services;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class ContentHtmlSanitizer
{
    private const ALLOWED_TAGS = ['div', 'p', 'br', 'strong', 'em', 'b', 'i', 'ul', 'ol', 'li', 'blockquote', 'a'];

    public function sanitize(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="cms-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('cms-root');
        if (! $root) {
            return '';
        }

        $this->cleanChildren($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    public function containsUnsafeLink(string $html): bool
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach ((new DOMXPath($document))->query('//a[@href]') ?: [] as $link) {
            if (! $this->isSafeLink($link->getAttribute('href'))) {
                return true;
            }
        }

        return false;
    }

    private function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMComment) {
                $parent->removeChild($node);

                continue;
            }

            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                $parent->removeChild($node);

                continue;
            }

            $this->cleanChildren($node);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);

                continue;
            }

            $href = $tag === 'a' ? trim($node->getAttribute('href')) : '';
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $node->removeAttribute($attribute->name);
            }

            if ($tag === 'a') {
                if (! $this->isSafeLink($href)) {
                    while ($node->firstChild) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);

                    continue;
                }
                $node->setAttribute('href', $href);
                $node->setAttribute('target', '_blank');
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    private function isSafeLink(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
    }
}
