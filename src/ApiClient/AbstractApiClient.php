<?php

declare(strict_types=1);

namespace LogParser\ApiClient;

use LogParser\Exception\Http\ApiClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractApiClient
{
    private const DEFAULT_PATH = '/';

    protected HttpClientInterface $httpClient;

    /**
     * @param array<string, string> $options API request configuration
     */
    protected function callApi(
        string $path = self::DEFAULT_PATH,
        string $method = Request::METHOD_GET,
        array $options = []
    ): string {
        try {
            $response = $this->httpClient->request($method, $path, $options);

            return $response->getContent();
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
}
