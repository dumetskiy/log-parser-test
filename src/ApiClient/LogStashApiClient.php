<?php

declare(strict_types=1);

namespace LogParser\ApiClient;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LogStashApiClient extends AbstractApiClient
{
    public function __construct(HttpClientInterface $logstashClient) {
        $this->httpClient = $logstashClient;
    }

    /**
     * @param array<string, string> $options API request options
     */
    public function postLogstashLogs(array $options): void
    {
        $this->callApi(
            method: Request::METHOD_POST,
            options: $options
        );
    }
}
