<?php

namespace App\Tests\Unit\Documentation;

use App\Service\Documentation\DocSourceDefinition;
use App\Service\Documentation\HtmlSectionChunker;
use PHPUnit\Framework\TestCase;

class HtmlSectionChunkerTest extends TestCase
{
    private HtmlSectionChunker $chunker;

    protected function setUp(): void
    {
        $this->chunker = new HtmlSectionChunker();
    }

    public function testChunkerExtractsHeadingsAndContent(): void
    {
        $definition = new DocSourceDefinition(
            key: 'sample',
            sourceRepo: 'TYPO3-Documentation/Sample',
            gitUrl: 'https://example.invalid',
            gitRef: '13.4',
            version: '13.4',
            lang: 'en-us',
            documentationPath: 'Documentation',
            license: 'CC BY 4.0',
        );

        $fixture = __DIR__ . '/../../Fixtures/rendered/sample.html';
        $chunks = $this->chunker->chunkFile($definition, $fixture, 'Index.html');

        self::assertCount(3, $chunks);

        $intro = $chunks[0];
        self::assertSame('Introduction', $intro->title);
        self::assertSame('introduction', $intro->anchor);
        self::assertSame([], $intro->breadcrumbs);
        self::assertStringContainsString('Welcome to TYPO3 documentation.', $intro->contentMd);
        self::assertStringContainsString('Goal:', $intro->contentMd);

        $install = $chunks[1];
        self::assertSame('Install', $install->title);
        self::assertSame(['Introduction'], $install->breadcrumbs);
        self::assertSame('introduction', $install->parentAnchor);
        self::assertStringContainsString('- Clone repo', $install->contentMd);
        self::assertStringContainsString('`ddev start`', $install->contentMd);

        $requirements = $chunks[2];
        self::assertSame('Requirements', $requirements->title);
        self::assertSame(['Introduction', 'Install'], $requirements->breadcrumbs);
        self::assertStringContainsString('Ensure pgvector extension is installed.', $requirements->contentMd);
        self::assertStringContainsString('```', $requirements->contentMd);
    }
}
