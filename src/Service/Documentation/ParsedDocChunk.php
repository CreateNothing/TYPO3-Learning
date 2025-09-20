<?php

namespace App\Service\Documentation;

use DOMNode;
use DOMXPath;

final class ParsedDocChunk
{
    public string $contentMd = '';

    /**
     * @var list<DOMNode>
     */
    private array $nodes = [];

    /**
     * @param list<string> $breadcrumbs
     */
    public function __construct(
        public readonly string $sourceRepo,
        public readonly string $docPath,
        public readonly string $version,
        public readonly string $lang,
        public readonly string $title,
        public readonly string $anchor,
        public readonly int $headingLevel,
        public readonly ?string $parentAnchor,
        public readonly array $breadcrumbs,
        public readonly ?string $license,
        public ?array $payload,
    ) {
    }

    public function append(DOMNode $node): void
    {
        $this->nodes[] = $node;
    }

    public function finalise(DOMXPath $xpath): void
    {
        if ($this->payload === null) {
            $this->payload = [];
        }

        $this->payload += [
            'headingLevel' => $this->headingLevel,
            'parentAnchor' => $this->parentAnchor,
            'breadcrumbs' => $this->breadcrumbs,
            'htmlPath' => $this->docPath,
        ];

        $markdown = '';
        foreach ($this->nodes as $node) {
            $markdown .= $this->nodeToMarkdown($node, $xpath);
        }

        $this->contentMd = trim(preg_replace("/\n{3,}/", "\n\n", $markdown) ?? $markdown);
    }

    private function nodeToMarkdown(DOMNode $node, DOMXPath $xpath, int $indent = 0): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $this->normaliseWhitespace($node->textContent ?? '');
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }

        $tag = strtolower($node->nodeName);

        return match ($tag) {
            'p' => $this->convertParagraph($node, $xpath),
            'pre' => $this->convertPre($node),
            'code' => $this->convertInlineCode($node),
            'em', 'i' => '*' . $this->convertChildren($node, $xpath, $indent) . '*',
            'strong', 'b' => '**' . $this->convertChildren($node, $xpath, $indent) . '**',
            'ul' => $this->convertList($node, $xpath, $indent, false),
            'ol' => $this->convertList($node, $xpath, $indent, true),
            'li' => $this->convertListItem($node, $xpath, $indent),
            'a' => $this->convertLink($node, $xpath),
            'table' => $this->convertTable($node, $xpath),
            'thead', 'tbody', 'tr', 'th', 'td' => $this->convertChildren($node, $xpath, $indent),
            'img' => $this->convertImage($node),
            'blockquote' => $this->convertBlockquote($node, $xpath, $indent),
            'div', 'span', 'section', 'article' => $this->convertChildren($node, $xpath, $indent),
            'h2' => "\n\n## " . trim($this->convertChildren($node, $xpath, $indent)) . "\n\n",
            'h3' => "\n\n### " . trim($this->convertChildren($node, $xpath, $indent)) . "\n\n",
            'h4' => "\n\n#### " . trim($this->convertChildren($node, $xpath, $indent)) . "\n\n",
            default => $this->convertChildren($node, $xpath, $indent),
        };
    }

    private function convertParagraph(DOMNode $node, DOMXPath $xpath): string
    {
        $content = trim($this->convertChildren($node, $xpath));
        if ($content === '') {
            return '';
        }

        return $content . "\n\n";
    }

    private function convertPre(DOMNode $node): string
    {
        $code = rtrim($node->textContent ?? '');
        return "```\n" . $code . "\n```\n\n";
    }

    private function convertInlineCode(DOMNode $node): string
    {
        $text = trim($node->textContent ?? '');
        return '`' . str_replace('`', '\`', $text) . '`';
    }

    private function convertChildren(DOMNode $node, DOMXPath $xpath, int $indent = 0): string
    {
        $buffer = '';
        foreach ($node->childNodes as $child) {
            $buffer .= $this->nodeToMarkdown($child, $xpath, $indent);
        }

        return $buffer;
    }

    private function convertList(DOMNode $node, DOMXPath $xpath, int $indent, bool $ordered): string
    {
        $buffer = "";
        $index = 1;
        foreach ($node->childNodes as $child) {
            if (strtolower($child->nodeName) !== 'li') {
                continue;
            }

            $prefix = $ordered ? sprintf('%d. ', $index++) : '- ';
            $content = trim($this->convertChildren($child, $xpath, $indent + 1));
            $buffer .= str_repeat('  ', $indent) . $prefix . $content . "\n";
        }

        return $buffer . "\n";
    }

    private function convertListItem(DOMNode $node, DOMXPath $xpath, int $indent): string
    {
        return $this->convertChildren($node, $xpath, $indent);
    }

    private function convertLink(DOMNode $node, DOMXPath $xpath): string
    {
        $href = $node->attributes?->getNamedItem('href')?->nodeValue ?? '#';
        $text = trim($this->convertChildren($node, $xpath));
        if ($text === '') {
            $text = $href;
        }

        return sprintf('[%s](%s)', $text, $href);
    }

    private function convertTable(DOMNode $node, DOMXPath $xpath): string
    {
        $rows = [];
        foreach ($xpath->query('.//tr', $node) as $rowNode) {
            $cells = [];
            foreach ($xpath->query('./th|./td', $rowNode) as $cellNode) {
                $cells[] = trim($this->convertChildren($cellNode, $xpath));
            }
            if ($cells !== []) {
                $rows[] = '| ' . implode(' | ', $cells) . ' |';
            }
        }

        if ($rows === []) {
            return '';
        }

        if (count($rows) >= 2) {
            $columns = substr_count($rows[0], '|') - 1;
            $divider = '| ' . implode(' | ', array_fill(0, $columns, '---')) . ' |';
            array_splice($rows, 1, 0, [$divider]);
        }

        return implode("\n", $rows) . "\n\n";
    }

    private function convertImage(DOMNode $node): string
    {
        $src = $node->attributes?->getNamedItem('src')?->nodeValue ?? '';
        $alt = $node->attributes?->getNamedItem('alt')?->nodeValue ?? '';
        if ($src === '') {
            return '';
        }

        return sprintf('![%s](%s)', $alt, $src);
    }

    private function convertBlockquote(DOMNode $node, DOMXPath $xpath, int $indent): string
    {
        $content = trim($this->convertChildren($node, $xpath, $indent));
        if ($content === '') {
            return '';
        }

        $lines = array_map(static fn (string $line): string => '> ' . $line, explode("\n", $content));

        return implode("\n", $lines) . "\n\n";
    }

    private function normaliseWhitespace(string $value): string
    {
        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }
}
