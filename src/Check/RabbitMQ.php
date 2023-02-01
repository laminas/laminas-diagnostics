<?php

namespace Laminas\Diagnostics\Check;

use Exception;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use RuntimeException;

use function class_exists;
use function sprintf;

/**
 * Validate that a RabbitMQ service is running
 */
class RabbitMQ extends AbstractCheck
{
    /**
     * @param string  $host
     * @param integer $port
     * @param string  $user
     * @param string  $password
     * @param string  $vhost
     */
    public function __construct(
        protected $host = 'localhost',
        protected $port = 5672,
        protected $user = 'guest',
        protected $password = 'guest',
        protected $vhost = '/'
    ) {
    }

    /**
     * @return AMQPSocketConnection|AMQPConnection
     * @throws RuntimeException
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

        throw new RuntimeException('PhpAmqpLib is not installed');
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     *
     * @return Failure|Success
     */
    public function check()
    {
        try {
            $this->createClient()->channel();
            return new Success();
        } catch (Exception $e) {
            return new Failure(sprintf(
                'Failed to connect to RabbitMQ server. Reason: `%s`',
                $e->getMessage()
            ));
        }
    }
}
