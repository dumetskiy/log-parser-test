<?php

declare(strict_types=1);

namespace LogParser\Loader\Data;

use LogParser\Exception\FileSystem\FileSystemException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlDataLoader
{
    public function __construct(
        #[Autowire(value: '%kernel.project_dir%')]
        private readonly string $projectDirectory
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function getElasticaConfigurationData(): ?array
    {
        return $this->loadDataFromFile($this->buildYamlFilePath('elastica', 'configuration'));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadDataFromFile(string $filePath): ?array
    {
        try {
            return Yaml::parseFile($filePath);
        } catch (ParseException) {
            throw FileSystemException::create(sprintf(
                'Failed to parse YAML configuration from "%s"',
                $filePath
            ));
        }
    }

    private function buildYamlFilePath(string $category, string $fileName): string
    {
        return sprintf('%s/config/%s/%s.yaml', $this->projectDirectory, $category, $fileName);
    }
}
