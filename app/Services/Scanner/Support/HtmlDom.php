<?php

namespace App\Services\Scanner\Support;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;

class HtmlDom
{
    private DOMDocument $document;

    private DOMXPath $xpath;

    public function __construct(string $html)
    {
        $this->document = new DOMDocument();

        libxml_use_internal_errors(true);
        $this->document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $this->xpath = new DOMXPath($this->document);
    }

    public function query(string $expression): DOMNodeList
    {
        return $this->xpath->query($expression);
    }

    public function title(): ?string
    {
        $nodes = $this->query('//title');

        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : null;
    }

    public function links(): array
    {
        $links = [];

        foreach ($this->query('//a[@href]') as $node) {
            /** @var DOMElement $node */
            $links[] = [
                'href' => trim($node->getAttribute('href')),
                'text' => trim($node->textContent),
                'hidden' => $this->isHiddenNode($node),
            ];
        }

        return $links;
    }

    public function iframes(): array
    {
        $iframes = [];

        foreach ($this->query('//iframe') as $node) {
            /** @var DOMElement $node */
            $iframes[] = [
                'src' => trim($node->getAttribute('src')),
                'hidden' => $this->isHiddenNode($node),
            ];
        }

        return $iframes;
    }

    public function scripts(): array
    {
        $scripts = [];

        foreach ($this->query('//script') as $node) {
            /** @var DOMElement $node */
            $scripts[] = [
                'src' => trim($node->getAttribute('src')),
                'inline' => trim($node->textContent),
            ];
        }

        return $scripts;
    }

    public function metaTags(): array
    {
        $tags = [];

        foreach ($this->query('//meta') as $node) {
            /** @var DOMElement $node */
            $name = $node->getAttribute('name') ?: $node->getAttribute('property');

            if ($name === '') {
                continue;
            }

            $tags[] = [
                'name' => strtolower($name),
                'content' => $node->getAttribute('content'),
            ];
        }

        return $tags;
    }

    private function isHiddenNode(DOMElement $node): bool
    {
        $style = strtolower($node->getAttribute('style'));

        if (
            str_contains($style, 'display:none') || str_contains($style, 'display: none')
            || str_contains($style, 'visibility:hidden') || str_contains($style, 'visibility: hidden')
            || str_contains($style, 'opacity:0') || str_contains($style, 'opacity: 0')
        ) {
            return true;
        }

        $class = strtolower($node->getAttribute('class'));

        return str_contains($class, 'hidden') || str_contains($class, 'sr-only') || $node->getAttribute('hidden') !== '';
    }
}
