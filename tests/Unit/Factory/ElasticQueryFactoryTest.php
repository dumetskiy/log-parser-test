<?php

declare(strict_types=1);

namespace LogParser\Tests\Unit\Factory;

use Erichard\ElasticQueryBuilder\Query\BoolQuery;
use LogParser\DTO\Api\Request\LogCountRequestDTO;
use LogParser\Factory\Elastic\ElasticQueryFactory;
use PHPUnit\Framework\TestCase;

class ElasticQueryFactoryTest extends TestCase
{
    private ElasticQueryFactory $testInstance;

    protected function setUp(): void
    {
        $this->testInstance = new ElasticQueryFactory();
    }

    public function testBuildFromLogCountRequest(): void
    {
        $logCountRequestDTO = new LogCountRequestDTO();
        $logCountRequestDTO->serviceNames = ['name-a', 'name-b'];
        $logCountRequestDTO->startDate = new \DateTimeImmutable('01/01/2022');
        $logCountRequestDTO->endDate = new \DateTimeImmutable('01/01/2022');
        $logCountRequestDTO->statusCode = 201;

        $logCountQuery = $this->testInstance->buildFromLogCountRequest($logCountRequestDTO);
        $this->assertInstanceOf(BoolQuery::class, $logCountQuery);
        $queryData = $logCountQuery->build();
        $this->assertEquals($this->getExpectedQueryData(), $queryData);
    }

    public function getExpectedQueryData(): array
    {
        return [
            'bool' => [
                'must' => [
                    ['match' => ['http_code' => ['query' => '201']]],
                    ['bool' => [
                        'should' => [
                            ['match' => ['service_name' => ['query' => 'name-a']]],
                            ['match' => ['service_name' => ['query' => 'name-b']]]
                        ]
                    ]],
                    ['range' => [
                        'date_time' => ['gte' => '01/Jan/2022:00:00:00 +0000', 'lte' => '01/Jan/2022:23:59:59 +0000']]
                    ]
                ]
            ]
        ];
    }
}
