<?php

namespace App\Service\Documentation;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class GitRepositoryManager
{
    private string $basePath;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%env(default::APP_DOCS_BASE_PATH)%')]
        private readonly ?string $configuredBasePath,
    ) {
        $this->basePath = $this->resolveBasePath();
    }

    public function ensureUpToDate(DocSourceDefinition $definition): string
    {
        $targetPath = $this->basePath . DIRECTORY_SEPARATOR . $definition->key;
        $filesystem = new Filesystem();

        if (!$filesystem->exists($targetPath . DIRECTORY_SEPARATOR . '.git')) {
            $filesystem->mkdir($targetPath);
            $this->runProcess(['git', 'clone', $definition->gitUrl, '.'], $targetPath);
        }

        $this->runProcess(['git', 'fetch', '--all', '--prune'], $targetPath);
        $this->runProcess(['git', 'reset', '--hard'], $targetPath);
        $this->runProcess(['git', 'clean', '-fdx'], $targetPath);
        $this->runProcess(['git', 'checkout', $definition->gitRef], $targetPath);
        $this->runProcess(['git', 'pull', '--ff-only', 'origin', $definition->gitRef], $targetPath);

        return $targetPath;
    }

    private function resolveBasePath(): string
    {
        $basePath = $this->configuredBasePath;
        if ($basePath === null || $basePath === '') {
            $basePath = $this->projectDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'doc_sources';
        }

        $filesystem = new Filesystem();
        $filesystem->mkdir($basePath);

        return $basePath;
    }

    /**
     * @param list<string> $command
     */
    private function runProcess(array $command, string $workingDirectory): void
    {
        $process = new Process($command, $workingDirectory);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Git command "%s" failed in %s: %s',
                implode(' ', $command),
                $workingDirectory,
                $process->getErrorOutput() ?: $process->getOutput(),
            ));
        }
    }
}
