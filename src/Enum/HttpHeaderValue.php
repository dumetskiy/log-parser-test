<?php

declare(strict_types=1);

namespace LogParser\Enum;

enum HttpHeaderValue: string
{
    case CONTENT_TYPE_RAW_LOG = 'application/raw-log';
    case CONTENT_TYPE_JSON_LOG = 'application/json-lines';
}
