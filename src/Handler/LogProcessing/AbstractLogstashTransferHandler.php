<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\ApiClient\LogStashApiClient;
use LogParser\Exception\Http\ApiClientException;
use LogParser\Exception\Http\LogStashCommunicationException;
use LogParser\ValueObject\LogBatchConfiguration;
use Psr\Log\LoggerInterface;

abstract class AbstractLogstashTransferHandler implements LogProcessingHandlerInterface
{
    public function __construct(
        readonly private LoggerInterface $logger,
        readonly private LogStashApiClient $logstashClient,
    ) {}

    /**
     * @param array<string, mixed> $options API request options
     */
    abstract public function prepareRequestOptions(array &$options): void;

    public function __invoke(LogBatchConfiguration $logBatchConfiguration): void
    {
        try {
            $this->logger->info('Sending logs batch data to LogStash...');
            $requestOptions = [
                'body' => $logBatchConfiguration->logLines,
            ];

            $this->prepareRequestOptions($requestOptions);

            $this->logstashClient->postLogstashLogs($requestOptions);
        } catch (ApiClientException $exception) {
            throw LogStashCommunicationException::create('Failed to send logs to LogStash');
        }
    }
}
