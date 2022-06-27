<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\Attribute\LogProcessingHandler;
use LogParser\Enum\HttpHeaderValue;
use LogParser\Enum\LogProcessingStrategy;

#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::PARSE_AND_PROXY,
    executionOrder: 2
)]
class JsonLogstashTransferHandler extends AbstractLogstashTransferHandler
{
    /**
     * {@inheritdoc}
     */
    public function prepareRequestOptions(array &$options): void
    {
        $options['headers']['Content-Type'] = HttpHeaderValue::CONTENT_TYPE_JSON_LOG->value;
    }
}
