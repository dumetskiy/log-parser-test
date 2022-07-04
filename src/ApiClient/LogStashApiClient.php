<?php

declare(strict_types=1);

namespace LogParser\ApiClient;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LogStashApiClient extends AbstractApiClient
{
    public function __construct(HttpClientInterface $logstashClient, SerializerInterface $serializer) {
        $this->httpClient = $logstashClient;

        parent::__construct($serializer);
    }

    /**
     * @param array<string, string> $options API request options
     */
    public function postLogstashLogs(array $options): void
    {
        $logstashResponse = $this->callApi(
            method: Request::METHOD_POST,
            options: $options
        );
    }
}
