<?php

namespace App\Service;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OllamaEmbeddingClient
{
    private string $endpoint;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(OLLAMA_BASE_URI)%')]
        string $baseUri,
    ) {
        $this->endpoint = rtrim($baseUri, '/');
    }

    /**
     * @return float[]
     */
    public function embed(string $model, string $input, ?int $dimensions = null): array
    {
        $payload = [
            'model' => $model,
            'input' => $input,
        ];

        if ($dimensions !== null) {
            $payload['options'] = ['dimension' => $dimensions];
        }

        $response = $this->httpClient->request('POST', $this->endpoint . '/api/embeddings', [
            'json' => $payload,
        ]);

        $data = $response->toArray(false);

        if (!isset($data['embedding']) || !is_array($data['embedding'])) {
            throw new RuntimeException('Unexpected response from Ollama embedding API.');
        }

        return array_map(static fn ($value) => (float) $value, $data['embedding']);
    }
}
