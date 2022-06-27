<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\Attribute\LogProcessingHandler;
use LogParser\DataParser\LogLineParser;
use LogParser\Enum\LogProcessingStrategy;
use LogParser\Exception\LogParserException;
use LogParser\ValueObject\LogBatchConfiguration;
use Psr\Log\LoggerInterface;

#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::PARSE_AND_PROXY,
    executionOrder: 1
)]
class JsonLogNormalizationHandler implements LogProcessingHandlerInterface
{
    public function __construct(
        readonly private LoggerInterface $logger,
        readonly private LogLineParser $logLineParser
    ) {}

    public function __invoke(LogBatchConfiguration $logBatchConfiguration): void
    {
        $this->logger->info('Transforming raw logs into JSON...');
        $rawLogLines = explode(\PHP_EOL, $logBatchConfiguration->logLines);
        $normalizedJsonLines = '';

        foreach ($rawLogLines as $rawLogLine) {
            $jsonLogLine = $this->transformLogLineIntoJson($rawLogLine);

            if (!$jsonLogLine) {
                continue;
            }

            $normalizedJsonLines .= $jsonLogLine . \PHP_EOL;
        }

        $normalizedJsonLines = rtrim($normalizedJsonLines, \PHP_EOL);
        $logBatchConfiguration->logLines = $normalizedJsonLines;
    }

    private function transformLogLineIntoJson(string $rawLogLine): ?string
    {
        try {
            if (empty($rawLogLine)) {
                return null;
            }

            $logLineData = $this->logLineParser->parse($rawLogLine);

            return json_encode(
                $logLineData,
                \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR
            );
        } catch (LogParserException) {
            $this->logger->warning(sprintf(
                'Failed to parse log line "%s". Skipping.',
                $rawLogLine
            ));
        } catch (\Throwable) {
            $this->logger->warning(sprintf(
                'Unhandled error occurred when parsing log line "%s". Skipping.',
                $rawLogLine
            ));
        }

        return null;
    }
}
