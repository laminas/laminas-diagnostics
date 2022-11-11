<?php

namespace Laminas\Diagnostics\Check;

use Elastic\Elasticsearch\Client as ElasticClient;
use Elastic\Elasticsearch\ClientBuilder as ElasticClientBuilder;
use Elasticsearch\Client as ElasticsearchClient;
use Elasticsearch\ClientBuilder as ElasticsearchClientBuilder;
use Exception;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

use function microtime;
use function trim;

/**
 * Ensures a connection to ElasticSearch is possible and the cluster health is 'green'
 */
class ElasticSearch extends AbstractCheck
{
    /** @var array{hosts: string[], username?: string, password?: string} */
    private array $elasticSettings;

    /** @var ElasticClientBuilder|ElasticsearchClientBuilder */
    private $clientBuilder;

    /**
     * @param array{hosts: string[], username?: string, password?: string} $elasticSettings
     * @param ElasticClientBuilder|ElasticsearchClientBuilder              $clientBuilder
     */
    public function __construct(array $elasticSettings, $clientBuilder)
    {
        $this->elasticSettings = $elasticSettings;
        $this->clientBuilder   = $clientBuilder;
    }

    public function check(): ResultInterface
    {
        $client = $this->getClient();

        try {
            $startTime    = microtime(true);
            $health       = $this->getClusterHealth($client);
            $responseTime = microtime(true) - $startTime;
        } catch (Exception $e) {
            return new Failure("Unable to connect to elasticsearch: " . $e->getMessage());
        }

        $serviceData = [
            "responseTime" => $responseTime,
        ];

        if ($health === 'green') {
            return new Success("Cluster status green", $serviceData);
        }

        if ($health === 'yellow') {
            return new Warning("Cluster status yellow", $serviceData);
        }

        return new Failure("Cluster status red", $serviceData);
    }

    /**
     * @return ElasticClient|ElasticsearchClient
     */
    protected function getClient()
    {
        $this->clientBuilder->setHosts($this->elasticSettings['hosts']);
        if (isset($this->elasticSettings['username'], $this->elasticSettings['password'])) {
            $this->clientBuilder->setBasicAuthentication(
                $this->elasticSettings['username'],
                $this->elasticSettings['password']
            );
        }

        return $this->clientBuilder->build();
    }

    /**
     * @param ElasticClient|ElasticsearchClient $client
     * @throws Exception
     */
    protected function getClusterHealth($client): string
    {
        if ($client instanceof ElasticsearchClient) {
            $health = $client->cat()->health();

            return $health[0]["status"] ?? "red";
        }

        if ($client instanceof ElasticClient) {
            $health = $client->cat()->health(['h' => 'status'])->asString();

            return trim($health);
        }

        return 'unknown';
    }
}
