<?php

namespace App\Tests\Integration;

use App\Service\OllamaEmbeddingClient;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown();
    }

    public function testSearchReturnsResults(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $connection = $container->get(Connection::class);
        $connection->executeStatement('TRUNCATE doc_chunks RESTART IDENTITY CASCADE');

        $connection->executeStatement(
            'INSERT INTO doc_chunks (source_repo, doc_path, version, lang, title, anchor, content_md, created_at, embedding) VALUES (:source_repo, :doc_path, :version, :lang, :title, :anchor, :content_md, NOW(), :embedding)',
            [
                'source_repo' => 'typo3/docs',
                'doc_path' => 'getting-started/install',
                'version' => '13.0',
                'lang' => 'en',
                'title' => 'Install TYPO3',
                'anchor' => 'introduction',
                'content_md' => 'Install TYPO3 by following the official guide.',
                'embedding' => $this->vectorLiteral(0.1),
            ],
        );

        $mock = $this->createMock(OllamaEmbeddingClient::class);
        $mock->method('embed')->willReturn($this->vectorArray(0.1));
        $container->set(OllamaEmbeddingClient::class, $mock);

        $client->request('GET', '/api/search', ['q' => 'TYPO3 installation']);

        $this->assertResponseIsSuccessful();
        $json = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('TYPO3 installation', $json['query']);
        $this->assertSame(1, $json['count']);
        $this->assertSame('Install TYPO3', $json['results'][0]['title']);
    }

    /**
     * @return float[]
     */
    private function vectorArray(float $value): array
    {
        return array_fill(0, 768, $value);
    }

    private function vectorLiteral(float $value): string
    {
        $values = array_fill(0, 768, number_format($value, 6, '.', ''));

        return '[' . implode(',', $values) . ']';
    }
}
