<?php

declare(strict_types=1);

namespace LogParser\Manager;

use LogParser\ApiClient\ElasticApiClient;
use LogParser\Exception\Configuration\ConfigurationException;
use LogParser\Exception\LogParserException;
use LogParser\Factory\ValueObject\ElasticIndexConfigurationFactory;
use LogParser\Loader\Data\YamlDataLoader;
use Psr\Log\LoggerInterface;

class ElasticManager
{
    public function __construct(
        private readonly ElasticIndexConfigurationFactory $indexConfigurationFactory,
        private readonly YamlDataLoader $dataLoader,
        private readonly ElasticApiClient $elasticApiClient,
        private readonly LoggerInterface $logger
    ) {}

    public function initElasticSchema(): void
    {
        try {
            $elasticConfiguration = $this->dataLoader->getElasticaConfigurationData();
            $indexesConfigurations = $this->indexConfigurationFactory->buildFromConfigurationData($elasticConfiguration);

            foreach ($indexesConfigurations as $indexConfiguration) {
                $this->elasticApiClient->configureIndex($indexConfiguration);
            }
        } catch (LogParserException $exception) {
            $this->logger->error($exception->getMessage());

            throw ConfigurationException::create(
                'Failed to init elastic schema. Is the index already configured?'
            );
        }
    }
}
