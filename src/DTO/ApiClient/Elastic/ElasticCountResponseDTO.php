<?php

declare(strict_types=1);

namespace LogParser\DTO\ApiClient\Elastic;

use LogParser\DTO\ApiClient\ApiResponseInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;

class ElasticCountResponseDTO implements ApiResponseInterface
{
    public int $count;

    #[SerializedName('_shards')]
    public ElasticShardsDTO $shards;
}
