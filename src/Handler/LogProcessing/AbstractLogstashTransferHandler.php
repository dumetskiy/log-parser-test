<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\Exception\Http\LogStashCommunicationException;
use LogParser\ValueObject\LogBatchConfiguration;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractLogstashTransferHandler implements LogProcessingHandlerInterface
{
    public function __construct(
        readonly private LoggerInterface $logger,
        readonly private HttpClientInterface $logstashClient,
    ) {}

    public function prepareRequestOptions(array &$options): void
    {
        // Implement any request options mutations here
    }

    public function __invoke(LogBatchConfiguration $logBatchConfiguration): void
    {
        try {
            $this->logger->notice('Sending logs batch data to LogStash...');
            $requestOptions = [
                'body' => $logBatchConfiguration->logLines
            ];

            $this->prepareRequestOptions($requestOptions);

            $response = $this->logstashClient->request(Request::METHOD_POST, '/', $requestOptions);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw LogStashCommunicationException::create(sprintf(
                    'Logstash send logs call resulted in %d status code',
                    $response->getStatusCode()
                ));
            }
        } catch (TransportExceptionInterface) {
            throw LogStashCommunicationException::create(
                'Failed to send logs to LogStash'
            );
        }
    }
}
