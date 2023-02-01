<?php

namespace LaminasTest\Diagnostics;

use InvalidArgumentException;
use Laminas\Diagnostics\Check\DiskFree;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use PHPUnit\Framework\TestCase;

use function count;
use function disk_free_space;
use function is_writable;
use function sys_get_temp_dir;

/**
 * Bytes conversion tests borrowed from Jerity project:
 *     https://github.com/jerity/jerity/blob/master/tests/Util/NumberTest.php
 *     authors:   Dave Ingram <dave@dmi.me.uk>, Nick Pope <nick@nickpope.me.uk>
 *     license:   http://creativecommons.org/licenses/BSD/ CC-BSD
 *     copyright: Copyright (c) 2010, Dave Ingram, Nick Pope
 *
 * @covers \Laminas\Diagnostics\Check\DiskFree
 */
final class DiskFreeTest extends TestCase
{
    public static function stringToBytesProvider(): array
    {
        $values         = [1, 10, 12.34];
        $prefixSymbol   = ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'K', 'M', 'G'];
        $prefixName     = [
            '',
            'kilo',
            'mega',
            'giga',
            'tera',
            'peta',
            'exa',
            'zetta',
            'kibi',
            'mebi',
            'gibi',
            'tebi',
            'pebi',
            'exbi',
            'kilo',
            'mega',
            'giga',
        ];
        $multiplierBase = [10, 10, 10, 10, 10, 10, 10, 10, 2, 2, 2, 2, 2, 2, 2, 2, 2];
        $multiplierExp  = [0, 3, 6, 9, 12, 15, 18, 21, 10, 20, 30, 40, 50, 60, 10, 20, 30];
        $data           = [];
        foreach ($values as $value) {
            for ($i = 0; $i < count($prefixSymbol); $i++) {
                $v       = $value * $multiplierBase[$i] ** $multiplierExp[$i];
                $jedec   = $i >= count($prefixSymbol) - 4;
                $data[]  = ["{$value}{$prefixSymbol[$i]}B", $jedec, $v];
                $data[]  = ["{$value}{$prefixSymbol[$i]}Bps", $jedec, $v];
                $data[]  = ["{$value}{$prefixSymbol[$i]}b", $jedec, $v / 8];
                $data[]  = ["{$value}{$prefixSymbol[$i]}bps", $jedec, $v / 8];
                $data[]  = ["{$value} {$prefixSymbol[$i]}B", $jedec, $v];
                $data[]  = ["{$value} {$prefixSymbol[$i]}Bps", $jedec, $v];
                $data[]  = ["{$value} {$prefixSymbol[$i]}b", $jedec, $v / 8];
                $data[]  = ["{$value} {$prefixSymbol[$i]}bps", $jedec, $v / 8];
                $postfix = $value === 1 ? '' : 's';
                $data[]  = ["{$value}{$prefixName[$i]}byte{$postfix}", $jedec, $v];
                $data[]  = ["{$value}{$prefixName[$i]}bit{$postfix}", $jedec, $v / 8];
                $data[]  = ["{$value} {$prefixName[$i]}byte{$postfix}", $jedec, $v];
                $data[]  = ["{$value} {$prefixName[$i]}bit{$postfix}", $jedec, $v / 8];
            }
        }

        return $data;
    }

    public static function stringToBytesExceptionProvider(): array
    {
        return [
            ['Not a size.', false, InvalidArgumentException::class],
            ['Not a size.', true, InvalidArgumentException::class],
            ['1 KB', false, InvalidArgumentException::class],
            ['1 TB', true, InvalidArgumentException::class],
        ];
    }

    public static function bytesToStringProvider(): array
    {
        return [
            [1_125_899_906_842_624, 5, '1 PiB'],
            [1_099_511_627_776,    5, '1 TiB'],
            [1_073_741_824,       5, '1 GiB'],
            [1_048_576,          5, '1 MiB'],
            [1024,             5, '1 KiB'],
            [999,              5, '999 B'],
            [1_351_079_888_211_148, 0, '1 PiB'],
            [1_319_413_953_331,    0, '1 TiB'],
            [1_288_490_190,       0, '1 GiB'],
            [1_258_291,          0, '1 MiB'],
            [1228,             0, '1 KiB'],
            [999,              0, '999 B'],
            [1_351_079_888_211_148, 1, '1.2 PiB'],
            [1_319_413_953_331,    1, '1.2 TiB'],
            [1_288_490_190,       1, '1.2 GiB'],
            [1_258_291,          1, '1.2 MiB'],
            [1228,             1, '1.2 KiB'],
            [999,              1, '999 B'],
        ];
    }

    /**
     * @dataProvider  stringToBytesProvider
     * @param int|float $c
     */
    public function testStringToBytes(string $a, bool $b, $c): void
    {
        self::assertEquals($c, DiskFree::stringToBytes($a, $b));
    }

    /**
     * @dataProvider  stringToBytesExceptionProvider
     * @psalm-param class-string $c
     */
    public function testStringToBytesException(string $a, bool $b, string $c): void
    {
        $this->expectException($c);

        DiskFree::stringToBytes($a, $b);
    }

    /** @dataProvider  bytesToStringProvider */
    public function testBytesToString(int $bytes, int $precision, string $string): void
    {
        self::assertSame($string, DiskFree::bytesToString($bytes, $precision));
    }

    public function testJitFreeSpace(): void
    {
        $tmp          = $this->getTempDir();
        $freeRightNow = disk_free_space($tmp);
        $check        = new DiskFree($freeRightNow * 0.5, $tmp);
        $result       = $check->check();

        self::assertInstanceof(Success::class, $result);
        $data = $result->getData();
        self::assertNotNull($data);
        self::assertSame($freeRightNow, $data['availability']['value']);
        self::assertSame('bytes', $data['availability']['valueType']);

        $freeRightNow = (int) disk_free_space($tmp);
        $check        = new DiskFree($freeRightNow + 1_073_741_824, $tmp);
        $result       = $check->check();

        self::assertInstanceof(Failure::class, $result);
        $data = $result->getData();
        self::assertNotNull($data);
        self::assertSame($freeRightNow, $data['availability']['value']);
        self::assertSame('bytes', $data['availability']['valueType']);
    }

    public function testSpaceWithStringConversion(): void
    {
        $tmp          = $this->getTempDir();
        $freeRightNow = disk_free_space($tmp);
        if ($freeRightNow < 1024) {
            self::markTestSkipped('There is less that 1024 bytes free in temp dir');
        }

        // give some margin of error
        $freeRightNowString = DiskFree::bytesToString((int) ($freeRightNow * 0.9));
        $check              = new DiskFree($freeRightNowString, $tmp);
        $result             = $check->check();

        self::assertInstanceof(Success::class, $result);
        $data = $result->getData();
        self::assertNotNull($data);
        self::assertSame($freeRightNow, $data['availability']['value']);
        self::assertSame('bytes', $data['availability']['valueType']);
    }

    public function testInvalidPathShouldReturnWarning(): void
    {
        $check  = new DiskFree(1024, __DIR__ . '/someImprobablePath99999999999999999');
        $result = $check->check();

        self::assertInstanceof(Warning::class, $result);
        self::assertNull($result->getData());
    }

    public function testInvalidSizeParamThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DiskFree(-1, $this->getTempDir());
    }

    public function testInvalidSizeParamThrowsException2(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DiskFree(-1, $this->getTempDir());
    }

    public function testInvalidSizeParamThrowsException3(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DiskFree([], $this->getTempDir());
    }

    public function testInvalidPathParamThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DiskFree(1024, 100);
    }

    protected function getTempDir(): string
    {
        // try to retrieve tmp dir
        $tmp = sys_get_temp_dir();

        // make sure there is any space there
        if (! $tmp || ! is_writable($tmp) || ! disk_free_space($tmp)) {
            self::markTestSkipped(
                'Cannot find a writable temporary directory with free disk space for Check\DiskFree tests'
            );
        }

        return $tmp;
    }
}
