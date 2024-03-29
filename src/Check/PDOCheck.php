<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result;
use PDO;
use PDOException;

use function sprintf;

/**
 * Ensures a connection to the MySQL server/database is possible.
 */
class PDOCheck implements CheckInterface
{
    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param int $timeout
     */
    public function __construct(private $dsn, private $username, private $password, private $timeout = 1)
    {
    }

    /**
     * @return Result\Failure|Result\Success
     */
    public function check()
    {
        $msg = 'Could not talk to database server';

        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => $this->timeout,
            ];

            $pdo = new PDO($this->dsn, $this->username, $this->password, $options);

            $status = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
            if (null !== $status) {
                return new Result\Success('Connection to database server was successful.');
            }
        } catch (PDOException $e) {
            // skip to failure
            $msg .= ', e: ' . $e->getCode();
        }

        return new Result\Failure($msg);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return sprintf('Check if %s can be reached', $this->dsn);
    }
}
