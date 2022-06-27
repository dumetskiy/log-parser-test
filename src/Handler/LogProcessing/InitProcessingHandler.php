<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\Attribute\LogProcessingHandler;
use LogParser\Enum\LogProcessingStrategy;
use LogParser\ValueObject\LogBatchConfiguration;
use Psr\Log\LoggerInterface;

#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::PARSE_AND_PROXY,
    executionOrder: 0
)]
#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::RAW_LOG_PROXY,
    executionOrder: 0
)]
class InitProcessingHandler implements LogProcessingHandlerInterface
{
    public function __construct(
        readonly private LoggerInterface $logger
    ) {}

    public function __invoke(LogBatchConfiguration $logBatchConfiguration): void
    {
        $this->logger->info(sprintf(
            'Started processing chunk %d [lines %d - %d]...',
            $logBatchConfiguration->batchId,
            $logBatchConfiguration->startLine,
            $logBatchConfiguration->startLine + $logBatchConfiguration->linesCount,
        ));
        $logBatchConfiguration->startStopWatch();
    }
}
