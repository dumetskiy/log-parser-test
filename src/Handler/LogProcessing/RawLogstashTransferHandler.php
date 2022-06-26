<?php

declare(strict_types=1);

namespace LogParser\Handler\LogProcessing;

use LogParser\Attribute\LogProcessingHandler;
use LogParser\Enum\HttpHeaderValue;
use LogParser\Enum\LogProcessingStrategy;

#[LogProcessingHandler(LogProcessingStrategy::RAW_LOG_PROXY, 1)]
class RawLogstashTransferHandler extends AbstractLogstashTransferHandler
{
    public function prepareRequestOptions(array &$options): void
    {
        $options['headers']['Content-Type'] = HttpHeaderValue::CONTENT_TYPE_RAW_LOG->value;
    }
}
