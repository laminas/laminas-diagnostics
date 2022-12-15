<?php

namespace Laminas\Diagnostics\Check;

use Exception;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

use function array_merge;
use function microtime;
use function trim;

/**
 * Ensures a connection to ElasticSearch is possible and the cluster health is 'green'
 */
class ElasticSearch extends GuzzleHttpService
{
    /**
     * @param array  $headers An array of headers used to create the request
     * @param array  $options An array of guzzle options used to create the request
     * @param null|GuzzleClientInterface $guzzle Instance of guzzle to use
     */
    public function __construct(string $elasticSearchUrl, array $headers = [], array $options = [], $guzzle = null)
    {
        $elasticSearchUrl .= '/_cat/health?h=status';

        parent::__construct($elasticSearchUrl, $headers, $options, 200, null, $guzzle);
    }

    public function check(): ResultInterface
    {
        try {
            $startTime    = microtime(true);
            $response     = $this->guzzle->send($this->request, array_merge($this->options));
            $health       = trim((string) $response->getBody());
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
}
