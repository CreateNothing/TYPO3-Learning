<?php

namespace App\Tests\Integration;

use App\Command\UpdateDocChunkEmbeddingsCommand;
use App\Service\OllamaEmbeddingClient;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateDocChunkEmbeddingsCommandTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testCommandFillsMissingEmbeddings(): void
    {
        $container = self::getContainer();
        $connection = $container->get(Connection::class);
        $connection->executeStatement('TRUNCATE doc_chunks RESTART IDENTITY CASCADE');

        $connection->executeStatement(
            'INSERT INTO doc_chunks (source_repo, doc_path, version, lang, title, anchor, content_md, created_at) VALUES (:source_repo, :doc_path, :version, :lang, :title, :anchor, :content_md, NOW())',
            [
                'source_repo' => 'typo3/docs',
                'doc_path' => 'search/vector',
                'version' => '13.0',
                'lang' => 'en',
                'title' => 'Vector Search',
                'anchor' => null,
                'content_md' => 'Vector search uses embeddings.',
            ],
        );

        $mock = $this->createMock(OllamaEmbeddingClient::class);
        $mock->method('embed')->willReturn(array_fill(0, 768, 0.2));
        $container->set(OllamaEmbeddingClient::class, $mock);

        $application = new Application(self::$kernel);
        $command = $application->find('app:embedding:update');
        $tester = new CommandTester($command);
        $tester->execute(['--limit' => 1]);

        $embedding = $connection->fetchOne('SELECT embedding FROM doc_chunks WHERE id = 1');
        self::assertNotNull($embedding);
        self::assertStringContainsString('0.2', $embedding);
    }
}
