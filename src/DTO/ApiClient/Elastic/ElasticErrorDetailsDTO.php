<?php

declare(strict_types=1);

namespace LogParser\DTO\ApiClient\Elastic;

use LogParser\DTO\ApiClient\ApiResponseInterface;

class ElasticErrorDetailsDTO implements ApiResponseInterface
{
    public string $type;

    public string $reason;

    public ?string $index = null;

    public ?string $indexUuid = null;
}
