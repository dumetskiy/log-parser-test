<?php

declare(strict_types=1);

namespace LogParser\Enum;

enum LogProcessingStrategy: string
{
    case RAW_LOG_PROXY = 'raw_proxy';
    case PARSE_AND_PROXY = 'parse_and_proxy';
}
