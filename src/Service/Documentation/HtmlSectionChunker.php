<?php

namespace App\Service\Documentation;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;

final class HtmlSectionChunker
{
    /**
     * @return list<ParsedDocChunk>
     */
    public function chunkFile(DocSourceDefinition $definition, string $htmlPath, string $relativeDocPath): array
    {
        $html = file_get_contents($htmlPath);
        if ($html === false) {
            throw new RuntimeException(sprintf('Unable to read rendered file "%s"', $htmlPath));
        }

        $dom = new DOMDocument();
        $internal = libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($internal);

        $root = $this->resolveContentRoot($dom);
        if (!$root instanceof DOMElement) {
            return [];
        }

        $xpath = new DOMXPath($dom);

        $sections = [];
        $current = null;
        $stack = [];

        foreach ($this->iterateNodes($root) as $node) {
            if ($node instanceof DOMElement && $this->isHeading($node)) {
                $anchor = $this->resolveAnchor($node);
                if ($anchor === null) {
                    continue;
                }

                $level = $this->headingLevel($node);
                $title = $this->normaliseHeadingText($node);

                while ($stack !== [] && $stack[array_key_last($stack)]['level'] >= $level) {
                    array_pop($stack);
                }

                $breadcrumbs = array_map(static fn (array $item): string => $item['title'], $stack);
                $parentAnchor = $stack !== [] ? $stack[array_key_last($stack)]['anchor'] : null;

                $section = new ParsedDocChunk(
                    sourceRepo: $definition->sourceRepo,
                    docPath: $relativeDocPath,
                    version: $definition->version,
                    lang: $definition->lang,
                    title: $title,
                    anchor: $anchor,
                    headingLevel: $level,
                    parentAnchor: $parentAnchor,
                    breadcrumbs: $breadcrumbs,
                    license: $definition->license,
                    payload: null,
                );

                $sections[] = $section;
                $current = $section;
                $stack[] = ['anchor' => $anchor, 'level' => $level, 'title' => $title];
                continue;
            }

            if ($current instanceof ParsedDocChunk) {
                $current->append($node);
            }
        }

        foreach ($sections as $section) {
            $section->finalise($xpath);
        }

        $result = array_filter($sections, static fn (ParsedDocChunk $chunk): bool => $chunk->contentMd !== '');

        return array_values($result);
    }

    private function resolveContentRoot(DOMDocument $dom): ?DOMElement
    {
        $xpath = new DOMXPath($dom);
        $candidates = [
            '//*[@role="main"]',
            '//main',
            '//article[contains(@class, "article")]//div[contains(@class, "content")]',
            '//body',
        ];

        foreach ($candidates as $query) {
            $node = $xpath->query($query)->item(0);
            if ($node instanceof DOMElement) {
                return $node;
            }
        }

        return null;
    }

    /**
     * @return iterable<DOMNode>
     */
    private function iterateNodes(DOMElement $root): iterable
    {
        for ($node = $root->firstChild; $node !== null; $node = $node->nextSibling) {
            yield $node;
        }
    }

    private function isHeading(DOMElement $element): bool
    {
        return in_array(strtolower($element->tagName), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true);
    }

    private function headingLevel(DOMElement $element): int
    {
        return (int) substr($element->tagName, 1);
    }

    private function resolveAnchor(DOMElement $element): ?string
    {
        $id = $element->getAttribute('id');
        if ($id !== '') {
            return $id;
        }

        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && $child->hasAttribute('id')) {
                return $child->getAttribute('id');
            }
        }

        return null;
    }

    private function normaliseHeadingText(DOMElement $element): string
    {
        $text = trim($element->textContent ?? '');
        $text = preg_replace('/\p{Zs}+¶$/u', '', $text) ?? $text;
        return trim($text);
    }
}
