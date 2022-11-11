<?php

namespace LaminasTest\Diagnostics;

use Elastic\Elasticsearch\ClientBuilder as ElasticClientBuilder;
use Elasticsearch\Client as ElasticsearchClient;
use Elasticsearch\ClientBuilder as ElasticsearchClientBuilder;
use Elasticsearch\Namespaces\CatNamespace as ElasticsearchCat;
use Exception;
use Laminas\Diagnostics\Check\ElasticSearch;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\WarningInterface;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

use function class_exists;

/** @covers \Laminas\Diagnostics\Check\ElasticSearch */
class ElasticSearchTest extends TestCase
{
    /**
     * @dataProvider healthStatusProvider
     * @param class-string<ResultInterface> $expectedResult
     * @throws Exception
     */
    public function testElasticSearch(string $clusterStatus, string $expectedResult): void
    {
        if (class_exists(ElasticsearchClientBuilder::class)) {
            $clientBuilder = $this->getElasticSearchClientBuilder($clusterStatus);
        } elseif (class_exists(ElasticClientBuilder::class)) {
            $clientBuilder = $this->getElasticClientBuilder($clusterStatus);
        } else {
            static::markTestSkipped("Missing elasticsearch client, unable to test ElasticSearch check");
        }

        $check = new ElasticSearch(
            ['hosts' => ['127.0.0.1'], 'username' => 'user', 'password' => 'pass'],
            $clientBuilder
        );

        static::assertInstanceOf($expectedResult, $check->check());
    }

    /**
     * @return array{array{string, class-string<ResultInterface>}}
     */
    public function healthStatusProvider(): array
    {
        return [
            ["green", SuccessInterface::class],
            ["yellow", WarningInterface::class],
            ["red", FailureInterface::class],
            ["unknown", FailureInterface::class],
        ];
    }

    /**
     * @return ElasticsearchClientBuilder&MockBuilder
     */
    private function getElasticSearchClientBuilder(string $expectedStatus): ElasticsearchClientBuilder
    {
        $cat = $this->createMock(ElasticsearchCat::class);
        $cat->expects(self::once())->method('health')->willReturn([0 => ["status" => $expectedStatus]]);

        $client = $this->createMock(ElasticsearchClient::class);
        $client->expects(self::once())->method('cat')->willReturn($cat);

        $clientBuilder = $this->createMock(ElasticsearchClientBuilder::class);
        $clientBuilder->expects(self::once())->method('build')->willReturn($client);

        return $clientBuilder;
    }

    private function getElasticClientBuilder(string $expectedStatus): ElasticClientBuilder
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($expectedStatus);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $clientBuilder = ElasticClientBuilder::create();
        $clientBuilder->setHttpClient($httpClient);

        return $clientBuilder;
    }
}
