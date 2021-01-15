<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result;
use PDO;

/**
 * Ensures a connection to the MySQL server/database is possible.
 */
class PDOCheck extends AbstractCheck
{
    private $dsn;
    private $password;
    private $username;
    private $timeout;

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param int $timeout
     *
     * @return self
     */
    public function __construct($dsn, $username, $password, $timeout = 1)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->timeout = $timeout;
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
                PDO::ATTR_TIMEOUT => $this->timeout
            ];

            $pdo = new PDO($this->dsn, $this->username, $this->password, $options);

            $status = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
            if (null !== $status) {
                return new Result\Success('Connection to database server was successful.');
            }
        } catch (\PDOException $e) {
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
