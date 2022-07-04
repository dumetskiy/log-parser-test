<?php

declare(strict_types=1);

namespace LogParser\Tests\Unit\DataParser;

use LogParser\DataParser\LogLineParser;
use PHPUnit\Framework\TestCase;

class LogLineParserTest extends TestCase
{
    private LogLineParser $testInstance;

    protected function setUp(): void
    {
        $this->testInstance = new LogLineParser();
    }

    /**
     * @dataProvider getValidLogLinesData
     */
    public function testValidLogLines(string $logLine, array $dataSet): void
    {
        $this->assertEquals($dataSet, $this->testInstance->parse($logLine));
    }

    /**
     * @dataProvider getInvalidLogLinesData
     */
    public function testInvalidLogLines(string $logLine): void
    {
        $this->expectExceptionMessage('Failed to parse log line.');
        $this->testInstance->parse($logLine);
    }

    public function getInvalidLogLinesData(): array
    {
        return [
            ['USER-SERVICE [17/Aug/2021:09:21:53 +0000] "POST /users HTTP/1.1" 201'],
            ['USER-SERVICE - - "17/Aug/2021:09:21:53 +0000" "POST /users HTTP/1.1" 201'],
            ['USER-SERVICE - - "17/Aug/2021:09:21:53 +0000" POST /users HTTP/1.1 201'],
            ['USER-SERVICE - - "17/Aug/2021:09:21:53 +0000" POST /users HTTP/1.1 CODE'],
            ['$pec!al_ch*r$ - - "17/Aug/2021:09:21:53 +0000" POST /users HTTP/1.1 CODE'],
        ];
    }

    public function getValidLogLinesData(): array
    {
        return [
            [
                'USER-SERVICE - - [17/Aug/2021:09:21:53 +0000] "POST /users HTTP/1.1" 201',
                [
                    'service_name' => 'USER-SERVICE',
                    'date_time' => '17/Aug/2021:09:21:53 +0000',
                    'http_code' => '201',
                ],
            ],
            [
                'INVOICE-SERVICE - - [17/Aug/2021:09:22:59 +0000] "POST /invoices HTTP/1.1" 400',
                [
                    'service_name' => 'INVOICE-SERVICE',
                    'date_time' => '17/Aug/2021:09:22:59 +0000',
                    'http_code' => '400',
                ],
            ],
            [
                'DELTA - - [09/Sep/2001:01:47:34 +0000] "POST value-62c28a3aa54be" 203',
                [
                    'service_name' => 'DELTA',
                    'date_time' => '09/Sep/2001:01:47:34 +0000',
                    'http_code' => '203',
                ],
            ],
        ];
    }
}
