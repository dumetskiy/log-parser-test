<?php

declare(strict_types=1);

namespace LogParser\Manager;

use LogParser\ApiClient\ElasticApiClient;
use LogParser\DTO\Api\Request\LogCountRequestDTO;
use LogParser\DTO\ApiClient\Elastic\ElasticCountResponseDTO;
use LogParser\Exception\Configuration\ConfigurationException;
use LogParser\Factory\Elastic\ElasticQueryFactory;
use LogParser\Factory\ValueObject\ElasticIndexConfigurationFactory;
use LogParser\Loader\Data\YamlDataLoader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ElasticManager
{
    public function __construct(
        #[Autowire(value: '%log_parser.elastic.index_name%')]
        private readonly string $logsIndexName,
        private readonly ElasticIndexConfigurationFactory $indexConfigurationFactory,
        private readonly YamlDataLoader $dataLoader,
        private readonly ElasticApiClient $elasticApiClient,
        private readonly ElasticQueryFactory $elasticQueryFactory
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

    public function getLogsCount(LogCountRequestDTO $logCountRequestDTO): ElasticCountResponseDTO
    {
        $countQuery = $this->elasticQueryFactory->buildFromLogCountRequest($logCountRequestDTO);

        return $this->elasticApiClient->getEntriesCount(
            $this->logsIndexName,
            $countQuery->isEmpty() ? null : $countQuery->build()
        );
    }
}
