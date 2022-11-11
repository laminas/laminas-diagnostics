<?php

namespace LaminasTest\Diagnostics;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Laminas\Diagnostics\Check\ElasticSearch;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\WarningInterface;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \Laminas\Diagnostics\Check\ElasticSearch */
class ElasticSearchTest extends TestCase
{
    /**
     * @dataProvider healthStatusProvider
     * @param class-string<ResultInterface> $expectedResult
     * @throws Exception
     */
    public function testElasticSearch(string $clusterStatus, string $expectedResult): void
    {
        $mockHandler = new MockHandler([new Response(200, [], $clusterStatus)]);
        $mockClient  = new Client(['handler' => $mockHandler]);
        $check       = new ElasticSearch('localhost:9200', [], [], $mockClient);

        static::assertInstanceOf($expectedResult, $check->check());
    }

    /**
     * @return array{array{string, class-string<ResultInterface>}}
     */
    public function healthStatusProvider(): array
    {
        return [
            ["green\n", SuccessInterface::class],
            ["yellow\n", WarningInterface::class],
            ["red\n", FailureInterface::class],
            ["unknown\n", FailureInterface::class],
        ];
    }
}
