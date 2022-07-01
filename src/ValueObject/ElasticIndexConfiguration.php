<?php

declare(strict_types=1);

namespace LogParser\ValueObject;

class ElasticIndexConfiguration
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(
        public readonly string $index,
        public readonly array $configuration
    ) {}
}
