<?php

declare(strict_types=1);

namespace LogParser\Factory\Elastic;

use Erichard\ElasticQueryBuilder\Query\BoolQuery;
use Erichard\ElasticQueryBuilder\Query\MatchQuery;
use Erichard\ElasticQueryBuilder\Query\RangeQuery;
use LogParser\DTO\Api\Request\LogCountRequestDTO;
use LogParser\Enum\ElasticIndexProperty;

class ElasticQueryFactory
{
    private const DATE_FORMAT = 'd/M/Y:H:i:s O';

    public function buildFromLogCountRequest(LogCountRequestDTO $logCountRequestDTO): BoolQuery
    {
        $query = new BoolQuery();

        if (null !== $logCountRequestDTO->statusCode) {
            $query->addMust(new MatchQuery(
                field: ElasticIndexProperty::HTTP_CODE->value,
                query: (string) $logCountRequestDTO->statusCode
            ));
        }

        if (null !== $logCountRequestDTO->serviceNames) {
            $serviceNameQuery = new BoolQuery();

            foreach ($logCountRequestDTO->serviceNames as $serviceName) {
                $serviceNameQuery->addShould(new MatchQuery(
                    field: ElasticIndexProperty::SERVICE_NAME->value,
                    query: $serviceName
                ));
            }

            $query->addMust($serviceNameQuery);
        }

        if (null !== $logCountRequestDTO->startDate || null !== $logCountRequestDTO->endDate) {
            $startDate = $logCountRequestDTO->startDate instanceof \DateTimeImmutable
                ? $logCountRequestDTO->startDate->setTime(0, 0)->format(self::DATE_FORMAT)
                : null;
            $endDate = $logCountRequestDTO->endDate instanceof \DateTimeImmutable
                ? $logCountRequestDTO->endDate->setTime(23, 59, 59)->format(self::DATE_FORMAT)
                : null;
            $query->addMust(new RangeQuery(
                field: ElasticIndexProperty::DATE_TIME->value,
                lte: $endDate,
                gte: $startDate
            ));
        }

        return $query;
    }
}
