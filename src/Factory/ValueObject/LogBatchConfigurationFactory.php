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
    ): ?LogBatchConfiguration {
        try {
            $batchLinesCount = 0;
            $logFile = $currentBatch->parseOperationConfiguration->logFile;
            $batchContent = '';
            $reachedEof = false;

            if ($logFile->eof()) {
                return null;
            }

            while (($logLine = $logFile->fgets()) !== false) {
                if (empty($logLine)) {
                    continue;
                }

                $batchContent .= $logLine;

                if ($batchLinesCount++ === $batchLinesLimit - 1) {
                    break;
                }

                if ($logFile->eof()) {
                    $reachedEof = true;

                    break;
                }
            }

            return new LogBatchConfiguration(
                parseOperationConfiguration: $currentBatch->parseOperationConfiguration,
                batchId: $currentBatch->batchId + 1,
                startLine: $currentBatch->startLine + $currentBatch->linesCount,
                linesCount: $batchLinesCount,
                logLines: $batchContent,
                reachedEof: $reachedEof
            );
        } catch (\RuntimeException) {
            return null;
        }
    }
}
