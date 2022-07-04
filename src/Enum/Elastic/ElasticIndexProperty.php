<?php

declare(strict_types=1);

namespace LogParser\Enum\Elastic;

enum ElasticIndexProperty: string
{
    case HTTP_CODE = 'http_code';
    case SERVICE_NAME = 'service_name';
    case DATE_TIME = 'date_time';
}
