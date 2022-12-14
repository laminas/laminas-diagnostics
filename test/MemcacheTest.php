<?php

namespace LaminasTest\Diagnostics;

use Generator;
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
        $this->expectExceptionMessage("Invalid port number -11211 - expecting an unsigned integer");
        new Memcache('127.0.0.1', -11211);
    }

    /**
     * @dataProvider providerValidConstructorArguments
     */
    public function testConstructor(array $arguments): void
    {
        new Memcache(...$arguments);

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
                'unix:///run/memcached/memcached.sock',
                0,
            ],
        ];
    }
}
