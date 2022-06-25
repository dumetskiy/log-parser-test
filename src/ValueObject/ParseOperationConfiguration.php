<?php

declare(strict_types=1);

namespace LogParser\ValueObject;

use LogParser\Enum\LogProcessingStrategy;

class ParseOperationConfiguration
{
    public function __construct(
        public readonly \SplFileObject $logFile,
        public readonly LogProcessingStrategy $processingStrategy,
        public readonly int $offset
    ) {}
}
