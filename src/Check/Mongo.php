<?php

namespace Laminas\Diagnostics\Check;

use Exception;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use MongoClient;
use MongoDB\Client as MongoDBClient;
use RuntimeException;

class Mongo extends AbstractCheck
{
    /**
     * @var string
     */
    private $connectionUri;

    /**
     * @param string $connectionUri
     */
    public function __construct($connectionUri = 'mongodb://127.0.0.1/')
    {
        $this->connectionUri = $connectionUri;
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
     * @return array|\Iterator
     *
     * @throws \RuntimeException
     * @throws \MongoDB\Driver\Exception
     * @throws \MongoConnectionException
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
