<?php

declare(strict_types=1);

namespace LogParser\Command;

use LogParser\Exception\LogParserException;
use LogParser\Manager\ElasticManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'log-parser:elastic:init',
    description: 'Creates and sets up elastic indexes and mappings'
)]
class ElasticSchemaInitCommand extends Command
{
    public function __construct(
        readonly private ElasticManager $elasticManager,
        readonly private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->logger->notice('Setting up elastic indexes and mappings...');
            $this->elasticManager->initElasticSchema();
            $this->logger->notice('Configuration gathered successfully!');

            return self::SUCCESS;
        } catch (LogParserException $exception) {
            $this->logger->error($exception->getMessage());
        } catch (\Throwable $throwable) {
            throw $throwable;
            $this->logger->error('Unhandled error occurred');
        }

        return self::FAILURE;
    }
}
