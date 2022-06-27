<?php

declare(strict_types=1);

namespace LogParser\ApiClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ElasticApiClient extends AbstractApiClient
{
    public function __construct(HttpClientInterface $elasticaClient) {
        $this->httpClient = $elasticaClient;
    }
}
