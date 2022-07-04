<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\Attribute\LogProcessingHandler;
use LogParser\Enum\LogProcessingStrategy;
use LogParser\ValueObject\LogBatchConfiguration;
use Psr\Log\LoggerInterface;

#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::PARSE_AND_PROXY,
    executionOrder: 3
)]
#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::RAW_LOG_PROXY,
    executionOrder: 2
)]
class FinishProcessingHandler implements LogProcessingHandlerInterface
{
    private const SECOND_TO_MILLISECOND_RATIO = 1000;
    private const BYTE_TO_MEGABYTE_RATIO = 1048576;

    public function __construct(
        readonly private LoggerInterface $logger
    ) {}

    public function __invoke(LogBatchConfiguration $logBatchConfiguration): void
    {
        $processingEvent = $logBatchConfiguration->endStopWatch();

        $this->logger->notice(sprintf(
            'Batch %d [lines %s-%s] processed in %s sec, %sMB used. Offset: %s',
            $logBatchConfiguration->batchId,
            number_format($logBatchConfiguration->startLine, thousands_separator: ' '),
            number_format($logBatchConfiguration->getBatchEndLine(), thousands_separator: ' '),
            number_format($processingEvent->getDuration() / self::SECOND_TO_MILLISECOND_RATIO, 3),
            number_format($processingEvent->getMemory() / self::BYTE_TO_MEGABYTE_RATIO, 1),
            number_format($logBatchConfiguration->getBatchEndLine(), thousands_separator: ' ')
        ));
    }
}
