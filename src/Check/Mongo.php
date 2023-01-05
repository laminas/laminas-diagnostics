<?php

namespace Laminas\Diagnostics\Check;

use Exception;
use Iterator;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use MongoClient;
use MongoConnectionException;
use MongoDB\Client as MongoDBClient;
use RuntimeException;

use function class_exists;
use function sprintf;

class Mongo extends AbstractCheck
{
    /**
     * @param string $connectionUri
     */
    public function __construct(private $connectionUri = 'mongodb://127.0.0.1/')
    {
    }

    /**
     * {@inheritdoc}
     */
    public function check()
    {
        try {
            $this->getListDBs();
        } catch (Exception $e) {
            return new Failure(sprintf(
                'Failed to connect to MongoDB server. Reason: `%s`',
                $e->getMessage()
            ));
        }

        return new Success();
    }

    /**
     * @return array|Iterator
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception
     * @throws MongoConnectionException
     */
    private function getListDBs()
    {
        if (class_exists(MongoDBClient::class)) {
            return (new MongoDBClient($this->connectionUri))->listDatabases();
        }

        if (class_exists(MongoClient::class)) {
            return (new MongoClient($this->connectionUri))->listDBs();
        }

        throw new RuntimeException('Neither the mongo extension or mongodb are installed');
    }
}
