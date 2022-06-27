<?php

declare(strict_types=1);

namespace LogParser\Command;

use LogParser\Enum\Command\Argument;
use LogParser\Enum\Command\Option;
use LogParser\Enum\LogProcessingStrategy;
use LogParser\Exception\LogParserException;
use LogParser\Factory\ValueObject\ParseConfigurationFactory;
use LogParser\Processor\LogParseOperationProcessor;
use LogParser\Utils\FileSystemUtils;
use LogParser\ValueObject\ParserConfiguration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'log-parser:parse',
    description: 'Parses the provided log file and stores it to the data storage'
)]
class ParseLogCommand extends Command
{
    public function __construct(
        #[Autowire(service: 'log_parser.configuration')]
        readonly private ParserConfiguration $parserConfiguration,
        readonly private ParseConfigurationFactory $operationConfigurationFactory,
        readonly private LogParseOperationProcessor $logParseOperationProcessor,
        readonly private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $logsDirectory = $this->parserConfiguration->logsDirectory;

        $this
            ->setDefinition(
                new InputDefinition([
                    new InputArgument(
                        name: Argument::FILENAME->value,
                        mode: InputArgument::REQUIRED,
                        description: 'The name of the log file to be parsed',
                        suggestedValues: FileSystemUtils::listProjectDirectoryFilenames($logsDirectory)
                    ),
                    new InputOption(
                        name: Option::LOG_PROCESSING_STRATEGY->value,
                        mode: InputOption::VALUE_OPTIONAL,
                        description: 'The strategy of input data handling ()',
                        default: LogProcessingStrategy::PARSE_AND_PROXY->value,
                    ),
                    new InputOption(
                        name: Option::OFFSET_LINES->value,
                        mode: InputOption::VALUE_OPTIONAL,
                        description: 'An amount of lines to be skipped from the beginning of the file',
                    ),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->logger->notice('Log parsing command triggered...');
            $this->logger->notice('Gathering operation configuration...');
            $operationConfiguration = $this->operationConfigurationFactory->create(
                fileName: $input->getArgument(Argument::FILENAME->value),
                processingStrategyHandle: $input->getOption(Option::LOG_PROCESSING_STRATEGY->value),
                offset: intval($input->getOption(Option::OFFSET_LINES->value)),
            );
            $this->logger->notice('Configuration gathered successfully!');

            $this->logParseOperationProcessor->process($operationConfiguration);

            return self::SUCCESS;
        } catch (LogParserException $exception) {
            $this->logger->error($exception->getMessage());
        } catch (\Throwable) {
            $this->logger->error('Unhandled error occurred');
        }

        return self::FAILURE;
    }
}
