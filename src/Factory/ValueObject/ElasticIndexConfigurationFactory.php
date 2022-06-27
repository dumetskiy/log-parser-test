<?php

declare(strict_types=1);

namespace LogParser\Factory\ValueObject;

use LogParser\ValueObject\ElasticIndexConfiguration;

class ElasticIndexConfigurationFactory
{
    /**
     * @param array<string, mixed> $configurationData
     * @return ElasticIndexConfiguration[]
     */
    final public function buildFromConfigurationData(array $configurationData): array
    {
        $indexesConfigurationData = $configurationData['indexes'] ?? [];

        return array_map(fn (string $index) => new ElasticIndexConfiguration(
            index: $index,
            configuration: $indexesConfigurationData[$index]
        ), array_keys($indexesConfigurationData));
    }
}
