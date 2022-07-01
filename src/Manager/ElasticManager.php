<?php

declare(strict_types=1);

namespace LogParser\Manager;

use LogParser\ApiClient\ElasticApiClient;
use LogParser\Exception\Configuration\ConfigurationException;
use LogParser\Factory\ValueObject\ElasticIndexConfigurationFactory;
use LogParser\Loader\Data\YamlDataLoader;

class ElasticManager
{
    public function __construct(
        private readonly ElasticIndexConfigurationFactory $indexConfigurationFactory,
        private readonly YamlDataLoader $dataLoader,
        private readonly ElasticApiClient $elasticApiClient
    ) {}

    public function initElasticSchema(): void
    {
        $elasticConfiguration = $this->dataLoader->getElasticaConfigurationData();

        if (!$elasticConfiguration) {
            throw ConfigurationException::create('Failed to fetch indexes configuration data');
        }

        $indexesConfigurations = $this->indexConfigurationFactory->buildFromConfigurationData($elasticConfiguration);

        foreach ($indexesConfigurations as $indexConfiguration) {
            $this->elasticApiClient->configureIndex($indexConfiguration);
        }
    }
}
