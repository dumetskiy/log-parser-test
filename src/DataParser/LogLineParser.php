<?php

namespace LogParser\DataParser;

use LogParser\Enum\LogLineProperty;
use LogParser\Exception\LogParserException;

class LogLineParser
{
    private const RAW_LOG_REGEX
        = '/(?<service_name>[A-Za-z_\-]*) -.*- \[(?<date_time>[a-zA-Z0-9\/:\s\+]*)\] ".*" (?<http_code>\d*)/';

    /**
     * @return array<string, string> parsed log line data
     */
    public function parse(string $rawLogLine): array
    {
        preg_match(self::RAW_LOG_REGEX, $rawLogLine, $logLineData);

        // Removing all rudimentary values from matches
        $logLineData = array_intersect_key($logLineData, array_flip(LogLineProperty::allValues()));

        if (count(LogLineProperty::cases()) !== count($logLineData)) {
            // All log line properties should be in place to consider parse operation successful
            throw LogParserException::create('Failed to parse log line.');
        }

        return $logLineData;
    }
}
