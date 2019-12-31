<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Validate that a RabbitMQ service is running
 */
class RabbitMQ extends AbstractCheck
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var integer
     */
    protected $port;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $vhost;

    /**
     * @param string  $host
     * @param integer $port
     * @param string  $user
     * @param string  $password
     * @param string  $vhost
     */
    public function __construct(
        $host = 'localhost',
        $port = 5672,
        $user = 'guest',
        $password = 'guest',
        $vhost = '/'
    ) {
        $this->host     = $host;
        $this->port     = $port;
        $this->user     = $user;
        $this->password = $password;
        $this->vhost    = $vhost;
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     * @return Failure|Success
     */
    public function check()
    {
        if (! class_exists('PhpAmqpLib\Connection\AMQPConnection')) {
            return new Failure('PhpAmqpLib is not installed');
        }

        $conn = new AMQPConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost
        );

        $conn->channel();

        return new Success();
    }
}
