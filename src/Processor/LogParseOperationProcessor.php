<?php

declare(strict_types=1);

namespace LogParser\Processor;

use LogParser\Factory\Stack\LogHandlerStackFactory;
use LogParser\Factory\ValueObject\LogBatchConfigurationFactory;
use LogParser\Handler\LogProcessing\LogProcessingHandlerInterface;
use LogParser\ValueObject\LogBatchConfiguration;
use LogParser\ValueObject\ParseOperationConfiguration;
use LogParser\ValueObject\ParserConfiguration;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LogParseOperationProcessor
{
    public function __construct(
        #[Autowire(service: 'log_parser.configuration')]
        readonly private ParserConfiguration $parserConfiguration,
        readonly private LogHandlerStackFactory $logHandlerStackFactory,
        readonly private LogBatchConfigurationFactory $logBatchConfigurationFactory,
        readonly private LoggerInterface $logger,
    ) {}

    public function process(ParseOperationConfiguration $operationConfiguration): void
    {
        $this->logger->notice(sprintf(
            'Parsing file "%s" with offset %d and strategy "%s"...',
            $operationConfiguration->logFile->getFilename(),
            $operationConfiguration->offset,
            $operationConfiguration->processingStrategy->value
        ));

        // Building an initial (void) batch configuration - setting the caret to the correct line
        $logBatchConfiguration = $this->logBatchConfigurationFactory->buildInitialLogBatchConfiguration($operationConfiguration);

        // Fetching a handler stack for a selected processing strategy
        $handlerStack = $this->logHandlerStackFactory->getForStrategy($operationConfiguration->processingStrategy);

        do {
            $logBatchConfiguration = $this->logBatchConfigurationFactory->fetchNextLogBatch(
                $logBatchConfiguration,
                $this->parserConfiguration->batchSize
            );

            if (!$logBatchConfiguration instanceof LogBatchConfiguration || empty($logBatchConfiguration->logLines)) {
                break;
            }

            /** @var LogProcessingHandlerInterface $logHandler */
            foreach ($handlerStack as $logHandler) {
                // Running every log handler one by one over the log batch
                $logHandler($logBatchConfiguration);
            }
        } while (!$logBatchConfiguration->reachedEof);

        $this->logger->warning('End of file reached, aborting...');
    }
}
