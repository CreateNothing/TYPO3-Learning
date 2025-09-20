<?php

namespace App\Command;

use App\Service\Documentation\DocIngestionPipeline;
use App\Service\Documentation\DocSourceDefinition;
use App\Service\Documentation\DocSourceRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:docs:sync',
    description: 'Sync TYPO3 documentation sources, chunk render output, and persist records.',
)]
final class ImportDocsCommand extends Command
{
    public function __construct(
        private readonly DocSourceRegistry $sourceRegistry,
        private readonly DocIngestionPipeline $pipeline,
        #[Autowire('%env(string:EMBEDDING_MODEL)%')]
        private readonly string $embeddingModel,
        private readonly UpdateDocChunkEmbeddingsCommand $embeddingCommand,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('source', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specific source key(s) to sync');
        $this->addOption('skip-render', null, InputOption::VALUE_NONE, 'Reuse existing render output path if available');
        $this->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit number of chunks per source (debugging)');
        $this->addOption('skip-embeddings', null, InputOption::VALUE_NONE, 'Do not trigger embedding generation after import');
        $this->addOption('batch', null, InputOption::VALUE_OPTIONAL, 'Batch size for embedding update command', 50);
        $this->addOption('embedding-limit', null, InputOption::VALUE_OPTIONAL, 'Limit number of chunks for embedding update after import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceKeys = $input->getOption('source');
        $limit = $input->getOption('limit');
        $limit = $limit !== null ? (int) $limit : null;
        $skipRender = (bool) $input->getOption('skip-render');
        $skipEmbeddings = (bool) $input->getOption('skip-embeddings');
        $embeddingBatch = (int) $input->getOption('batch');
        $embeddingLimit = $input->getOption('embedding-limit');
        $embeddingLimit = $embeddingLimit !== null ? (int) $embeddingLimit : null;

        $definitions = $this->resolveDefinitions($sourceKeys);

        foreach ($definitions as $definition) {
            $output->writeln(sprintf('<info>Processing source %s (%s @ %s)</info>', $definition->key, $definition->gitUrl, $definition->gitRef));

            $result = $this->pipeline->ingest($definition, $skipRender, $limit);

            $output->writeln(sprintf(
                ' - Persisted %d chunk(s) (removed %d existing) for %s %s [%s]',
                $result['chunks'],
                $result['removed'],
                $result['source'],
                $result['version'],
                $result['lang'],
            ));
        }

        if (!$skipEmbeddings) {
            $output->writeln('<info>Triggering embedding command for fresh chunks…</info>');
            $this->embeddingCommand->runEmbedded($output, $embeddingBatch, $embeddingLimit, $this->embeddingModel);
        }

        return Command::SUCCESS;
    }

    /**
     * @param list<string>|string|null $sourceKeys
     * @return list<DocSourceDefinition>
     */
    private function resolveDefinitions(array|string|null $sourceKeys): array
    {
        if ($sourceKeys === null || $sourceKeys === []) {
            return $this->sourceRegistry->all();
        }

        if (is_string($sourceKeys)) {
            $sourceKeys = [$sourceKeys];
        }

        return array_map(fn (string $key): DocSourceDefinition => $this->sourceRegistry->get($key), $sourceKeys);
    }
}
