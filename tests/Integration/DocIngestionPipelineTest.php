<?php

namespace App\Tests\Integration;

use App\Service\Documentation\DocIngestionPipeline;
use App\Service\Documentation\DocSourceDefinition;
use App\Service\Documentation\GitRepositoryManager;
use App\Service\Documentation\GuidesRenderer;
use App\Service\Documentation\HtmlSectionChunker;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocIngestionPipelineTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->connection = $container->get(Connection::class);
        $this->connection->executeStatement('TRUNCATE doc_chunks RESTART IDENTITY CASCADE');
    }

    public function testIngestPersistsChunks(): void
    {
        $repoPath = sys_get_temp_dir() . '/doc_pipeline_' . uniqid();
        $renderPath = $repoPath . '/Documentation-GENERATED-temp';
        if (!mkdir($renderPath, 0777, true) && !is_dir($renderPath)) {
            self::fail('Unable to create render path for test.');
        }

        copy(
            __DIR__ . '/../Fixtures/rendered/sample.html',
            $renderPath . '/Index.html',
        );

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

        $projectDir = self::getContainer()->getParameter('kernel.project_dir');

        $pipeline = new DocIngestionPipeline(
            new GitRepositoryManager($projectDir, null),
            new GuidesRenderer('render-guides'),
            new HtmlSectionChunker(),
            $this->entityManager,
            $this->connection,
        );

        $result = $pipeline->ingestFromRenderedOutput($definition, $renderPath);

        self::assertSame('TYPO3-Documentation/Sample', $result['source']);
        self::assertSame(3, $result['chunks']);

        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM doc_chunks');
        self::assertSame(3, $count);

        /** @var array{title:string, anchor:string, content_md:string} $first */
        $first = $this->connection->fetchAssociative('SELECT title, anchor, content_md FROM doc_chunks ORDER BY id ASC LIMIT 1');
        self::assertSame('Introduction', $first['title']);
        self::assertSame('introduction', $first['anchor']);
        self::assertStringContainsString('Welcome to TYPO3 documentation.', $first['content_md']);
    }
}
