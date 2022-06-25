<?php

declare(strict_types=1);

namespace LogParser\ValueObject;

class ParserConfiguration
{
    public function __construct(
        public readonly string $logsDirectory,
        public readonly int $batchSize
    ) {}
}