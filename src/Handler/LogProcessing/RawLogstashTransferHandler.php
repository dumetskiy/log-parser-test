<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\Attribute\LogProcessingHandler;
use LogParser\Enum\HttpHeaderValue;
use LogParser\Enum\LogProcessingStrategy;

#[LogProcessingHandler(
    logProcessingStrategy: LogProcessingStrategy::RAW_LOG_PROXY,
    executionOrder: 1
)]
class RawLogstashTransferHandler extends AbstractLogstashTransferHandler
{
    /**
     * @inheritdoc
     */
    public function prepareRequestOptions(array &$options): void
    {
        $options['headers']['Content-Type'] = HttpHeaderValue::CONTENT_TYPE_RAW_LOG->value;
    }
}
