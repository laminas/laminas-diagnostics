<?php

namespace LaminasTest\Diagnostics;

use Generator;
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
        $this->expectExceptionMessage("Invalid port number -11211 - expecting an unsigned integer");
        new Memcached('127.0.0.1', -11211);
    }

    /**
     * @dataProvider providerValidConstructorArguments
     */
    public function testConstructor(array $arguments): void
    {
        new Memcached(...$arguments);

        $this->expectNotToPerformAssertions();
    }

    public function providerValidConstructorArguments(): Generator
    {
        yield 'no arguments' => [
            [],
        ];
        yield 'only host' => [
            ['127.0.0.1'],
        ];
        yield 'host and port' => [
            [
                '127.0.0.1',
                11211,
            ],
        ];
        yield 'unix socket' => [
            [
                '/run/memcached/memcached.sock',
                0,
            ],
        ];
    }
}
