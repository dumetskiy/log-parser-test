<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\ValueObject\LogBatchConfiguration;

/**
 * An interface of a handler performing a single step of the logs batch processing
 */
interface LogProcessingHandlerInterface
{
    /**
     * Implement the handler logic here
     *
     * @param LogBatchConfiguration $logBatchConfiguration An object holding the current state of the logs batch and
     * triggered operation configuration
     */
    public function __invoke(LogBatchConfiguration $logBatchConfiguration): void;
}
