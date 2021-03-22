<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;

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
     * @return AMQPSocketConnection|AMQPConnection
     *
     * @throws \RuntimeException
     */
    private function createClient()
    {
        if (class_exists(AMQPSocketConnection::class)) {
            return new AMQPSocketConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost
            );
        }

        if (class_exists(AMQPConnection::class)) {
            return new AMQPConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost
            );
        }

        throw new \RuntimeException('PhpAmqpLib is not installed');
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     * @return Failure|Success
     */
    public function check()
    {
        try {
            $this->createClient()->channel();
            return new Success();
        } catch (\Exception $e) {
            return new Failure(sprintf(
                'Failed to connect to RabbitMQ server. Reason: `%s`',
                $e->getMessage()
            ));
        }
    }

}
