<?php

namespace App\Command;

use App\Service\OllamaEmbeddingClient;
use App\Service\VectorFormatter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:embedding:update',
    description: 'Generate embeddings for doc chunks missing vectors using Ollama.',
)]
class UpdateDocChunkEmbeddingsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly OllamaEmbeddingClient $embeddingClient,
        private readonly VectorFormatter $vectorFormatter,
        #[Autowire('%env(EMBEDDING_MODEL)%')]
        private readonly string $embeddingModel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('batch', null, InputOption::VALUE_REQUIRED, 'Number of chunks to fetch per batch', 25);
        $this->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Maximum number of chunks to update');
        $this->addOption('dimension', null, InputOption::VALUE_OPTIONAL, 'Override embedding dimension');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int) $input->getOption('batch');
        $limitOpt = $input->getOption('limit');
        $maxToProcess = $limitOpt !== null ? (int) $limitOpt : null;
        $dimensionOpt = $input->getOption('dimension');
        $dimension = $dimensionOpt !== null ? (int) $dimensionOpt : null;

        $processed = $this->process($batchSize, $maxToProcess, $dimension, $output, $this->embeddingModel);

        $output->writeln(sprintf('<info>Embeddings updated for %d chunk(s).</info>', $processed));

        return Command::SUCCESS;
    }

    public function runEmbedded(OutputInterface $output, int $batchSize, ?int $maxToProcess, string $embeddingModel, ?int $dimension = null): void
    {
        $processed = $this->process($batchSize, $maxToProcess, $dimension, $output, $embeddingModel);
        $output->writeln(sprintf('<info>Embeddings updated for %d chunk(s).</info>', $processed));
    }

    private function process(int $batchSize, ?int $maxToProcess, ?int $dimension, OutputInterface $output, string $embeddingModel): int
    {
        $processed = 0;

        while (true) {
            $fetchLimit = $maxToProcess !== null ? min($batchSize, $maxToProcess - $processed) : $batchSize;
            if ($fetchLimit <= 0) {
                break;
            }

            $rows = $this->connection->fetchAllAssociative(
                'SELECT id, content_md FROM doc_chunks WHERE embedding IS NULL ORDER BY id ASC LIMIT :limit',
                ['limit' => $fetchLimit],
                ['limit' => ParameterType::INTEGER],
            );

            if ($rows === []) {
                break;
            }

            foreach ($rows as $row) {
                try {
                    $vector = $this->embeddingClient->embed($embeddingModel, (string) $row['content_md'], $dimension);
                    $literal = $this->vectorFormatter->toDatabaseLiteral($vector);

                    $this->connection->executeStatement(
                        'UPDATE doc_chunks SET embedding = :embedding WHERE id = :id',
                        [
                            'embedding' => $literal,
                            'id' => (int) $row['id'],
                        ],
                        [
                            'embedding' => ParameterType::STRING,
                            'id' => ParameterType::INTEGER,
                        ],
                    );

                    ++$processed;
                } catch (\Throwable $e) {
                    $output->writeln(sprintf('<error>Failed to embed chunk %d: %s</error>', $row['id'], $e->getMessage()));
                }

                if ($maxToProcess !== null && $processed >= $maxToProcess) {
                    break 2;
                }
            }
        }

        return $processed;
    }
}
