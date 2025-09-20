<?php

namespace App\Service\Documentation;

use InvalidArgumentException;

final class DocSourceRegistry
{
    /**
     * @var array<string, DocSourceDefinition>
     */
    private array $sources;

    /**
     * @param array<string, array<string, string|null>> $rawSources
     */
    public function __construct(array $rawSources)
    {
        $this->sources = [];

        foreach ($rawSources as $key => $config) {
            $this->sources[$key] = new DocSourceDefinition(
                key: $key,
                sourceRepo: $config['source_repo'] ?? throw new InvalidArgumentException(sprintf('Missing source_repo for %s', $key)),
                gitUrl: $config['git_url'] ?? throw new InvalidArgumentException(sprintf('Missing git_url for %s', $key)),
                gitRef: $config['git_ref'] ?? 'main',
                version: $config['version'] ?? throw new InvalidArgumentException(sprintf('Missing version for %s', $key)),
                lang: $config['lang'] ?? 'en-us',
                documentationPath: $config['documentation_path'] ?? 'Documentation',
                license: $config['license'] ?? null,
            );
        }
    }

    /**
     * @return list<DocSourceDefinition>
     */
    public function all(): array
    {
        return array_values($this->sources);
    }

    public function get(string $key): DocSourceDefinition
    {
        if (!isset($this->sources[$key])) {
            throw new InvalidArgumentException(sprintf('Unknown documentation source "%s"', $key));
        }

        return $this->sources[$key];
    }
}
