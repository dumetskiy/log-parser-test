<?php

declare(strict_types=1);

namespace LogParser\ApiClient;

use LogParser\DTO\ApiClient\Elastic\ElasticCountResponseDTO;
use LogParser\DTO\ApiClient\Elastic\ElasticErrorResponseDTO;
use LogParser\Exception\Http\ApiClientException;
use LogParser\ValueObject\ElasticIndexConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ElasticApiClient extends AbstractApiClient
{
    private const ENDPOINT_PATTERN_INDEX_COUNT_OPERATION = '/%s/_count';

    public function __construct(HttpClientInterface $elasticClient, SerializerInterface $serializer) {
        $this->httpClient = $elasticClient;

        parent::__construct($serializer);
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

        if (!$apiResponse->isSuccessful()) {
            /** @var ElasticErrorResponseDTO $errorResponseDTO */
            $errorResponseDTO = $this->denormalizeResponseData($apiResponse, ElasticErrorResponseDTO::class);

            throw ApiClientException::create(sprintf(
                'Index "%s" configuration failed: %s',
                $indexConfiguration->index,
                $errorResponseDTO->error?->reason
            ));
        }
    }

    /**
     * @param array<string, mixed>|null $query
     */
    public function getEntriesCount(string $index, ?array $query): ElasticCountResponseDTO
    {
        $body = null !== $query ? ['query' => $query] : null;
        $apiResponse = $this->callApi(
            path: sprintf(self::ENDPOINT_PATTERN_INDEX_COUNT_OPERATION, $index),
            options: [
                'body' => $body,
            ]
        );

        if (!$apiResponse->isSuccessful()) {
            /** @var ElasticErrorResponseDTO $errorResponseDTO */
            $errorResponseDTO = $this->denormalizeResponseData($apiResponse, ElasticErrorResponseDTO::class);

            throw ApiClientException::create(sprintf(
                'Index entries count failed: %s',
                $errorResponseDTO->error?->reason
            ));
        }

        /** @var ElasticCountResponseDTO $elasticCountResponse */
        $elasticCountResponse = $this->denormalizeResponseData($apiResponse, ElasticCountResponseDTO::class);

        return $elasticCountResponse;
    }
}
