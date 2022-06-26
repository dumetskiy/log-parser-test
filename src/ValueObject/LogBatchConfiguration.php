<?php

declare(strict_types=1);

namespace LogParser\ValueObject;

use Symfony\Component\Stopwatch\Stopwatch;

class LogBatchConfiguration
{
    private Stopwatch $stopwatch;

    public function __construct(
        public readonly ParseOperationConfiguration $parseOperationConfiguration,
        public readonly int $batchId = 1,
        public readonly int $startLine = 0,
        public readonly int $linesCount = 0,
        public readonly string $logLines = '',
        public readonly bool $reachedEof = false
    ) {
        $this->stopwatch = new Stopwatch();
    }

    public function startStopWatch(): void
    {
        $this->stopwatch->start($this->getStopWatchHandle());
    }

    public function endStopWatch(): void
    {
        $this->stopwatch->stop($this->getStopWatchHandle());
    }

    private function getStopWatchHandle(): string
    {
        return sprintf('batch_%d', $this->batchId);
    }
}
