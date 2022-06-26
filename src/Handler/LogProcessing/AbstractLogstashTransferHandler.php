<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\Attribute\LogProcessingHandler;
use LogParser\Enum\LogProcessingStrategy;
use LogParser\ValueObject\LogBatchConfiguration;
use Psr\Log\LoggerInterface;

abstract class AbstractLogstashTransferHandler implements LogProcessingHandlerInterface
{
    public function __construct(
        readonly private LoggerInterface $logger
    ) {}

    public function prepareRequestOptions(array &$options): void
    {
    }

    public function __invoke(LogBatchConfiguration $logBatchConfiguration): void
    {
        $this->logger->notice(sprintf(
            'Started processing chunk %d [lines %d - %d]...',
            $logBatchConfiguration->batchId,
            $logBatchConfiguration->startLine,
            $logBatchConfiguration->startLine + $logBatchConfiguration->linesCount,
        ));
        $logBatchConfiguration->startStopWatch();
    }
}
