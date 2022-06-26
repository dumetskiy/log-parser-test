<?php

declare(strict_types=1);

namespace LogParser\Enum;

enum LogLineProperty: string
{
    case DATE_TIME = 'date_time';
    case HTTP_CODE = 'http_code';
    case SERVICE_NAME = 'service_name';

    public static function allValues(): array
    {
        return array_map(fn (LogLineProperty $lineProperty) => $lineProperty->value, self::cases());
    }
}
