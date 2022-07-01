<?php

declare(strict_types=1);

namespace LogParser\DTO\ApiClient\Elastic;

use LogParser\DTO\ApiClient\ApiResponseInterface;

class ElasticErrorResponseDTO implements ApiResponseInterface
{
    public ?ElasticErrorDetailsDTO $error = null;
}
