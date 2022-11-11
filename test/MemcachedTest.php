<?php

namespace LaminasTest\Diagnostics;

use InvalidArgumentException;
use Laminas\Diagnostics\Check\Memcached;
use PHPUnit\Framework\TestCase;

/** @covers \Laminas\Diagnostics\Check\Memcached */
class MemcachedTest extends TestCase
{
    public function testHostValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot use array as host - expecting a string");
        new Memcached(['127.0.0.1']);
    }

    public function testPortValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid port number -11211 - expecting a positive integer");
        new Memcached('127.0.0.1', -11211);
    }
}
