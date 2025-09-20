<?php

namespace App\Service\Documentation;

use App\Entity\DocChunk;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class DocIngestionPipeline
{
    public function __construct(
        private readonly GitRepositoryManager $gitRepositoryManager,
        private readonly GuidesRenderer $guidesRenderer,
        private readonly HtmlSectionChunker $chunker,
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return array{source: string, version: string, lang: string, chunks: int, removed: int}
     */
    public function ingest(DocSourceDefinition $definition, bool $skipRender = false, ?int $limit = null): array
    {
        $repositoryPath = $this->gitRepositoryManager->ensureUpToDate($definition);
        $renderedPath = $skipRender
            ? $this->existingRenderPath($repositoryPath, $definition->documentationPath)
            : $this->guidesRenderer->render($repositoryPath, $definition->documentationPath);

        return $this->ingestFromRenderedOutput($definition, $renderedPath, $limit);
    }

    public function ingestFromRenderedOutput(DocSourceDefinition $definition, string $renderedPath, ?int $limit = null): array
    {
        $chunks = $this->collectChunks($definition, $renderedPath, $limit);

        return $this->persistChunks($definition, $chunks);
    }

    /**
     * @return list<ParsedDocChunk>
     */
    private function collectChunks(DocSourceDefinition $definition, string $renderedPath, ?int $limit): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($renderedPath, \FilesystemIterator::SKIP_DOTS),
        );

        $chunks = [];
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if (!str_ends_with($fileInfo->getFilename(), '.html')) {
                continue;
            }

            $relative = ltrim(str_replace($renderedPath, '', $fileInfo->getPathname()), DIRECTORY_SEPARATOR);
            if (str_starts_with($relative, '_') || str_contains($relative, DIRECTORY_SEPARATOR . '_')) {
                continue;
            }

            $parsed = $this->chunker->chunkFile(
                $definition,
                $fileInfo->getPathname(),
                str_replace(DIRECTORY_SEPARATOR, '/', $relative),
            );

            foreach ($parsed as $chunk) {
                $chunks[] = $chunk;
                if ($limit !== null && count($chunks) >= $limit) {
                    break 2;
                }
            }
        }

        return $chunks;
    }

    /**
     * @param list<ParsedDocChunk> $chunks
     * @return array{source: string, version: string, lang: string, chunks: int, removed: int}
     */
    private function persistChunks(DocSourceDefinition $definition, array $chunks): array
    {
        $this->connection->beginTransaction();
        try {
            $removed = $this->connection->executeStatement(
                'DELETE FROM doc_chunks WHERE source_repo = :source AND version = :version AND lang = :lang',
                [
                    'source' => $definition->sourceRepo,
                    'version' => $definition->version,
                    'lang' => $definition->lang,
                ],
            );

            $batchSize = 50;
            foreach ($chunks as $index => $chunk) {
                $entity = new DocChunk();
                $entity->setSourceRepo($chunk->sourceRepo);
                $entity->setDocPath($chunk->docPath);
                $entity->setVersion($chunk->version);
                $entity->setLang($chunk->lang);
                $entity->setLicense($chunk->license);
                $entity->setTitle($chunk->title);
                $entity->setAnchor($chunk->anchor);
                $entity->setContentMd($chunk->contentMd);
                $entity->setEmbeddingRef(null);
                $entity->setPayload($chunk->payload);

                $this->entityManager->persist($entity);

                if (($index + 1) % $batchSize === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->connection->commit();
        } catch (\Throwable $throwable) {
            $this->connection->rollBack();
            throw $throwable;
        }

        return [
            'source' => $definition->sourceRepo,
            'version' => $definition->version,
            'lang' => $definition->lang,
            'chunks' => count($chunks),
            'removed' => $removed,
        ];
    }

    private function existingRenderPath(string $repositoryPath, string $documentationPath): string
    {
        $path = rtrim($repositoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($documentationPath, DIRECTORY_SEPARATOR) . '-GENERATED-temp';

        $filesystem = new Filesystem();
        if (!$filesystem->exists($path)) {
            throw new \RuntimeException(sprintf('Expected rendered output at "%s" but none found.', $path));
        }

        return $path;
    }
}
