<?php

declare(strict_types=1);

namespace LogParser\ApiClient;

use LogParser\Exception\Http\ApiClientException;
use LogParser\ValueObject\ElasticIndexConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ElasticApiClient extends AbstractApiClient
{
    private const ENDPOINT_PATTERN_INDEX_CONFIGURATION = '/%s';

    public function __construct(HttpClientInterface $elasticClient) {
        $this->httpClient = $elasticClient;
    }

    public function configureIndex(ElasticIndexConfiguration $indexConfiguration): void
    {
        $apiResponse = $this->callApi(
            path: $indexConfiguration->index,
            method: Request::METHOD_PUT,
            options: [
                'body' => $indexConfiguration->configuration,
                'headers' => ['Content-Type' => 'application/json'],
            ]
        );
    }
}
