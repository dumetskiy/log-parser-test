<?php

declare(strict_types=1);

namespace LogParser\Factory\ValueObject;

use LogParser\ValueObject\LogBatchConfiguration;
use LogParser\ValueObject\ParseOperationConfiguration;

class LogBatchConfigurationFactory
{
    public function buildInitialLogBatchConfiguration(
        ParseOperationConfiguration $operationConfiguration
    ): LogBatchConfiguration {
        $operationConfiguration->logFile->seek($operationConfiguration->offset);

        return new LogBatchConfiguration(
            parseOperationConfiguration: $operationConfiguration,
            startLine: $operationConfiguration->offset
        );
    }

    public function fetchNextLogBatch(
        LogBatchConfiguration $currentBatch,
        int $batchLinesLimit
    ): LogBatchConfiguration {
        $batchLinesCount = 0;
        $logFile = $currentBatch->parseOperationConfiguration->logFile;
        $batchContent = '';
        $reachedEof = false;

        while($logLine = $logFile->fgets() !== false) {
            if ($logFile->eof()) {
                $reachedEof = true;

                break;
            }

            $batchContent .= $logLine . PHP_EOL;

            if ($batchLinesCount++ >= $batchLinesLimit) {
                break;
            }
        }

        return new LogBatchConfiguration(
            parseOperationConfiguration: $currentBatch->parseOperationConfiguration,
            batchId: ++$currentBatch->batchId,
            startLine: $currentBatch->startLine + $currentBatch->linesCount,
            linesCount: $batchLinesCount,
            logLines: $batchContent,
            reachedEof: $reachedEof
        );
    }
}