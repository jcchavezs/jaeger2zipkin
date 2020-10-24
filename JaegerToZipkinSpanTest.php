<?php

require_once './vendor/autoload.php';

use PHPUnit\Framework\TestCase;

final class JaegerToZipkinSpanTest extends TestCase
{
    public function testJaegerToZipkinSpan()
    {
        $jaegerSpan = [
            "traceID" => "0d2c51540ad782a1efe316fa94e69dcf",
            "spanID" => "5c754676109d4f08",
            "operationName" => "doSomething",
            "references" => [
                [
                    "refType" => "CHILD_OF",
                    "traceID" => "0d2c51540ad782a1efe316fa94e69dcf",
                    "spanID" => "9a58b069d9834019"
                ]
            ],
            "startTime" => 1602660520084194,
            "duration" => 894,
            "tags" => [
                [
                    "key" => "order_id",
                    "type" => "string",
                    "value" => "o1965832a"
                ],
                [
                    "key" => "is_enabled",
                    "type" => "bool",
                    "value" => false
                ],
                [
                    "key" => "status.code",
                    "type" => "int64",
                    "value" => 0
                ],
            ]
        ];

        $zipkinSpan = jaegerToZipkinSpan($jaegerSpan, "myservice", []);
        $this->assertEquals("0d2c51540ad782a1efe316fa94e69dcf", $zipkinSpan->getTraceId());
        $this->assertEquals("5c754676109d4f08", $zipkinSpan->getSpanID());
        $this->assertEquals("9a58b069d9834019", $zipkinSpan->getParentId());
        $this->assertEquals(1602660520084194, $zipkinSpan->getTimestamp());
        $this->assertEquals(894, $zipkinSpan->getDuration());
        $this->assertEquals("doSomething", $zipkinSpan->getName());
        $this->assertEquals([
            'order_id' => 'o1965832a',
            'is_enabled' => '',
            'status.code' => '0',
            'internal.span.format' => 'jaeger',
        ], $zipkinSpan->getTags());
    }
}
