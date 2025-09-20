<?php

namespace App\Controller\Api;

use App\Service\OllamaEmbeddingClient;
use App\Service\VectorFormatter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly OllamaEmbeddingClient $embeddingClient,
        private readonly VectorFormatter $vectorFormatter,
        #[Autowire('%env(string:EMBEDDING_MODEL)%')]
        private readonly string $embeddingModel,
    ) {
    }

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        if ($query === '') {
            return new JsonResponse(['error' => 'Missing query parameter "q".'], Response::HTTP_BAD_REQUEST);
        }

        $limit = (int) $request->query->get('limit', 10);
        $limit = max(1, min($limit, 50));

        $vector = $this->embeddingClient->embed($this->embeddingModel, $query);
        $literal = $this->vectorFormatter->toDatabaseLiteral($vector);

        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT
                id,
                source_repo,
                doc_path,
                version,
                lang,
                title,
                anchor,
                content_md,
                embedding <-> :embedding AS distance
            FROM doc_chunk
            WHERE embedding IS NOT NULL
            ORDER BY embedding <-> :embedding
            LIMIT :limit
            SQL,
            [
                'embedding' => $literal,
                'limit' => $limit,
            ],
            [
                'embedding' => ParameterType::STRING,
                'limit' => ParameterType::INTEGER,
            ],
        );

        $results = array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'sourceRepo' => $row['source_repo'],
                'docPath' => $row['doc_path'],
                'version' => $row['version'],
                'lang' => $row['lang'],
                'title' => $row['title'],
                'anchor' => $row['anchor'],
                'contentMd' => $row['content_md'],
                'distance' => (float) $row['distance'],
            ];
        }, $rows);

        return new JsonResponse([
            'query' => $query,
            'count' => count($results),
            'results' => $results,
        ]);
    }
}
