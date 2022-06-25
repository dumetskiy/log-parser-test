<?php

declare(strict_types=1);

namespace LogParser\Factory\ValueObject;

use LogParser\Enum\LogProcessingStrategy;
use LogParser\Exception\Command\CommandInputException;
use LogParser\Utils\FileSystemUtils;
use LogParser\ValueObject\ParseOperationConfiguration;
use LogParser\ValueObject\ParserConfiguration;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ParseConfigurationFactory
{
    public function __construct(
        #[Autowire(service: 'log_parser.configuration')]
        readonly private ParserConfiguration $parserConfiguration,
    ) {}

    final public function create(
        string $fileName,
        string $processingStrategyHandle,
        int $offset
    ): ParseOperationConfiguration {
        $logFileHandle = FileSystemUtils::getLogFileHandle($fileName, $this->parserConfiguration->logsDirectory);
        $processingStrategy = LogProcessingStrategy::tryFrom($processingStrategyHandle);

        if (!$processingStrategy instanceof LogProcessingStrategy) {
            throw CommandInputException::create(sprintf(
                'Processing strategy handle "%s" provided is not valid',
                $processingStrategyHandle
            ));
        }

        return new ParseOperationConfiguration($logFileHandle, $processingStrategy, $offset);
    }
}
