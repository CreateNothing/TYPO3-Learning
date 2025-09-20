<?php

namespace App\Service\Documentation;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class GuidesRenderer
{
    public function __construct(
        #[Autowire('%env(string:GUIDES_BINARY)%')]
        private readonly string $binary,
    ) {
    }

    public function render(string $sourceDir, string $documentationPath, ?string $outputDir = null): string
    {
        $absoluteSource = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($documentationPath, DIRECTORY_SEPARATOR);
        $filesystem = new Filesystem();

        if (!$filesystem->exists($absoluteSource)) {
            throw new RuntimeException(sprintf('Documentation path "%s" does not exist.', $absoluteSource));
        }

        $targetDir = $outputDir ?? $absoluteSource . '-GENERATED-temp';
        if ($filesystem->exists($targetDir)) {
            $filesystem->remove($targetDir);
        }
        $filesystem->mkdir($targetDir);

        $binary = $this->resolveBinary();

        $command = [
            $binary,
            '--config=' . trim($documentationPath, DIRECTORY_SEPARATOR),
            '--output=' . $targetDir,
            '--no-progress',
        ];

        $process = new Process($command, $sourceDir);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'render-guides failed: %s',
                $process->getErrorOutput() ?: $process->getOutput(),
            ));
        }

        return $targetDir;
    }

    private function resolveBinary(): string
    {
        if (str_contains($this->binary, DIRECTORY_SEPARATOR)) {
            if (!is_file($this->binary) || !is_executable($this->binary)) {
                throw new RuntimeException(sprintf('Configured GUIDES_BINARY "%s" is not executable.', $this->binary));
            }

            return $this->binary;
        }

        $finder = new ExecutableFinder();
        $extraPaths = [];

        $composerHome = getenv('COMPOSER_HOME');
        if (is_string($composerHome) && $composerHome !== '') {
            $extraPaths[] = rtrim($composerHome, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin';
        }

        $home = getenv('HOME');
        if (is_string($home) && $home !== '') {
            $home = rtrim($home, DIRECTORY_SEPARATOR);
            $extraPaths[] = $home . DIRECTORY_SEPARATOR . '.composer' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin';
            $extraPaths[] = $home . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin';
        }

        $resolved = $finder->find($this->binary, null, $extraPaths);

        if ($resolved === null) {
            throw new RuntimeException(sprintf(
                'Unable to locate "%s" binary. Install phpDocumentor/render-guides or set GUIDES_BINARY to its absolute path.',
                $this->binary,
            ));
        }

        return $resolved;
    }
}
