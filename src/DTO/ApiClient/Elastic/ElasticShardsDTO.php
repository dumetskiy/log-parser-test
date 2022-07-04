<?php

declare(strict_types=1);

namespace LogParser\DTO\ApiClient\Elastic;

use LogParser\DTO\ApiClient\ApiResponseInterface;

class ElasticShardsDTO implements ApiResponseInterface
{
    public int $total;

    public int $successful;

    public int $skipped;

    public int $failed;
}
