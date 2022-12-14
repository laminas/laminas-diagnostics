<?php

namespace Laminas\Diagnostics\Check;

use Exception;
use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Memcached as MemcachedService;

use function class_exists;
use function gettype;
use function is_string;
use function microtime;
use function sprintf;

/**
 * Check if MemCached extension is loaded and given server is reachable.
 */
class Memcached extends AbstractCheck
{
    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /**
     * @param string         $host The hostname of the memcache server. This parameter may
     *                             also specify other transports like /path/to/memcached.sock
     *                             to use UNIX domain sockets, in this case port must also be set to 0.
     * @param 0|positive-int $port The port where memcached is listening for connections.
     *                             Set this parameter to 0 when using UNIX domain sockets.
     * @throws InvalidArgumentException If host is not a string value.
     * @throws InvalidArgumentException If port is less than 0.
     */
    public function __construct($host = '127.0.0.1', $port = 11211)
    {
        if (! is_string($host)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot use %s as host - expecting a string',
                gettype($host)
            ));
        }

        $port = (int) $port;
        if ($port < 0) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port number %d - expecting an unsigned integer',
                $port
            ));
        }

        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @see CheckInterface::check()
     *
     * @return ResultInterface
     */
    public function check()
    {
        if (! class_exists('Memcached', false)) {
            return new Failure('Memcached extension is not loaded');
        }

        try {
            $memcached = new MemcachedService();
            $memcached->addServer($this->host, $this->port);

            $startTime = microtime(true);
            /** @var false|array<string, false|array<string, int|string>> $stats */
            $stats        = @$memcached->getStats();
            $responseTime = microtime(true) - $startTime;

            $authority   = sprintf('%s:%d', $this->host, $this->port);
            $serviceData = null;

            if (
                ! isset($stats[$authority])
                || false === $stats[$authority]
            ) {
                // Attempt a connection to make sure that the server is really down
                if (@$memcached->getLastDisconnectedServer() !== false) {
                    return new Failure(sprintf(
                        'No memcached server running at host %s on port %s',
                        $this->host,
                        $this->port
                    ));
                }
            } else {
                $serviceData = [
                    "responseTime" => $responseTime,
                    "connections"  => (int) $stats[$authority]['curr_connections'],
                    "uptime"       => (int) $stats[$authority]['uptime'],
                ];
            }
        } catch (Exception $e) {
            return new Failure($e->getMessage());
        }

        return new Success(sprintf(
            'Memcached server running at host %s on port %s',
            $this->host,
            $this->port
        ), $serviceData);
    }
}
