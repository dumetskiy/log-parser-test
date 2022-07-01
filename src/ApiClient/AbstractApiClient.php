<?php

declare(strict_types=1);

namespace LogParser\ApiClient;

use LogParser\DTO\ApiClient\ApiResponseInterface;
use LogParser\Exception\Http\ApiClientException;
use LogParser\ValueObject\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractApiClient
{
    private const DEFAULT_PATH = '/';

    protected HttpClientInterface $httpClient;

    public function __construct(private readonly SerializerInterface $serializer)
    {}

    /**
     * @param array<string, mixed> $options API request configuration
     */
    protected function callApi(
        string $path = self::DEFAULT_PATH,
        string $method = Request::METHOD_GET,
        array $options = []
    ): ApiResponse {
        try {
            if (isset($options['body']) && is_array($options['body'])) {
                $options['body'] = json_encode($options['body'], \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
            }

            $response = $this->httpClient->request($method, $path, $options);

            return new ApiResponse(
                code: $response->getStatusCode(),
                content: $response->getContent(false),
                headers: $response->getHeaders(false)
            );
        } catch (HttpExceptionInterface|TransportExceptionInterface $exception) {
            throw ApiClientException::create(
                message: sprintf(
                    'An API call at "%s" failed: %s',
                    $path,
                    $exception->getMessage()),
                previous: $exception
            );
        } catch (\Throwable) {
            throw ApiClientException::create(
                sprintf('An API call at "%s" resulted in unhandled error', $path)
            );
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function denormalizeResponseData(
        ApiResponse $apiResponse,
        string $responseClass,
        array $context = []
    ): ApiResponseInterface {
        try {
            /* @phpstan-ignore-next-line */
            return $this->serializer->denormalize(
                data: $apiResponse->getData(),
                type: $responseClass,
                context: $context
            );
        } catch (ExceptionInterface $exception) {
            throw ApiClientException::create(sprintf(
                'Failed to denormalize API response: %s',
                $exception->getMessage()
            ));
        }
    }
}
