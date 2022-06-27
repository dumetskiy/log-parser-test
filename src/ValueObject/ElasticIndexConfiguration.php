<?php

declare(strict_types=1);

namespace LogParser\ValueObject;

class ElasticIndexConfiguration
{
    public function __construct(
        public readonly string $index,
        public readonly array $configuration
    ) {}
}
