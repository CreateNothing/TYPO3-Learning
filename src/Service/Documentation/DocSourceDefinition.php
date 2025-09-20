<?php

namespace App\Service\Documentation;

final class DocSourceDefinition
{
    public function __construct(
        public readonly string $key,
        public readonly string $sourceRepo,
        public readonly string $gitUrl,
        public readonly string $gitRef,
        public readonly string $version,
        public readonly string $lang,
        public readonly string $documentationPath,
        public readonly ?string $license,
    ) {
    }
}
