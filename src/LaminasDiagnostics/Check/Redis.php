<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Predis\Client;

/**
 * Validate that a Redis service is running
 */
class Redis extends AbstractCheck
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @param string  $host
     * @param int $port
     */
    public function __construct($host = 'localhost', $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     * @return Failure|Success
     */
    public function check()
    {
        if (!class_exists('Predis\Client')) {
            return new Failure('Predis is not installed');
        }

        $client = new Client(array(
            'host' => $this->host,
            'port' => $this->port,
        ));

        $client->ping();

        return new Success();
    }
}
