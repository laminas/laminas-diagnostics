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
        $this->expectExceptionMessage("Invalid port number -11211 - expecting an unsigned integer");
        new Memcache('127.0.0.1', -11211);
    }

    /**
     * @dataProvider providerValidConstructorArguments
     * @param array<empty, empty>|array{string}|array{string, positive-int|0} $arguments
     */
    public function testConstructor(array $arguments): void
    {
        new Memcache(...$arguments);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @return non-empty-array<
     *     non-empty-string,
     *     array{array<empty, empty>|array{string}|array{string, positive-int|0}}
     * >
     */
    public static function providerValidConstructorArguments(): array
    {
        return [
            'no arguments'  => [[]],
            'only host'     => [
                ['127.0.0.1'],
            ],
            'host and port' => [
                [
                    '127.0.0.1',
                    11211,
                ],
            ],
            'unix socket'   => [
                [
                    'unix:///run/memcached/memcached.sock',
                    0,
                ],
            ],
        ];
    }
}
