<?php

namespace LaminasTest\Diagnostics;

use InvalidArgumentException;
use Laminas\Diagnostics\Check\Memcache;
use PHPUnit\Framework\TestCase;

/** @covers \Laminas\Diagnostics\Check\Memcache */
class MemcacheTest extends TestCase
{
    public function testHostValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot use array as host - expecting a string");
        new Memcache(['127.0.0.1']);
    }

    public function testPortValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid port number -11211 - expecting a positive integer");
        new Memcache('127.0.0.1', -11211);
    }
}
