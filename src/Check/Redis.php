<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Success;
use Predis\Client as PredisClient;
use Redis as RedisExtensionClient;
use RedisException;
use RuntimeException;

use function array_key_exists;
use function class_exists;
use function is_array;
use function microtime;

/**
 * Validate that a Redis service is running
 */
class Redis extends AbstractCheck
{
    /**
     * @param string $host
     * @param int $port
     */
    public function __construct(protected $host = 'localhost', protected $port = 6379, protected ?string $auth = null)
    {
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     *
     * @return Success
     */
    public function check()
    {
        $client = $this->createClient();

        $startTime = microtime(true);
        /** @var array $stats Redis client is not in `multi` mode, so methods will directly return there response */
        $stats        = $client->info();
        $responseTime = microtime(true) - $startTime;

        $successInformation = [
            "responseTime" => $responseTime,
        ];

        if (array_key_exists('connected_clients', $stats)) {
            $successInformation['connections'] = (int) $stats['connected_clients'];
        } elseif (
            array_key_exists('Clients', $stats) &&
            is_array($stats['Clients']) &&
            array_key_exists('connected_clients', $stats['Clients'])
        ) {
            $successInformation['connections'] = (int) $stats['Clients']['connected_clients'];
        }

        if (array_key_exists('uptime_in_seconds', $stats)) {
            $successInformation['uptime'] = (int) $stats['uptime_in_seconds'];
        } elseif (
            array_key_exists('Server', $stats) &&
            is_array($stats['Server']) && array_key_exists('uptime_in_seconds', $stats['Server'])
        ) {
            $successInformation['uptime'] = (int) $stats['Server']['uptime_in_seconds'];
        }

        return new Success(
            '',
            $successInformation
        );
    }

    /**
     * @return PredisClient|RedisExtensionClient
     * @throws RedisException
     * @throws RuntimeException
     */
    private function createClient()
    {
        if (class_exists(RedisExtensionClient::class)) {
            $client = new RedisExtensionClient();
            $client->connect($this->host, $this->port);

            if ($this->auth && false === $client->auth($this->auth)) {
                throw new RedisException('Failed to AUTH connection');
            }

            return $client;
        }

        if (class_exists(PredisClient::class)) {
            $parameters = [
                'host' => $this->host,
                'port' => $this->port,
            ];

            if ($this->auth) {
                $parameters['password'] = $this->auth;
            }

            return new PredisClient($parameters);
        }

        throw new RuntimeException('Neither the PHP Redis extension or Predis are installed');
    }
}
