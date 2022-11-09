<?php

namespace LaminasTest\Diagnostics;

use InvalidArgumentException;
use Laminas\Diagnostics\Check\DiskUsage;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\WarningInterface;
use PHPUnit\Framework\TestCase;

use function disk_free_space;
use function disk_total_space;
use function is_writable;
use function sys_get_temp_dir;

/** @covers \Laminas\Diagnostics\Check\DiskUsage */
final class DiskUsageTest extends TestCase
{
    /**
     * @dataProvider invalidArgumentProvider
     * @param int|string $warningThreshold
     * @param int|string $criticalThreshold
     * @param string|array $path
     */
    public function testInvalidArguments($warningThreshold, $criticalThreshold, $path): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DiskUsage($warningThreshold, $criticalThreshold, $path);
    }

    public function testCheck(): void
    {
        $df = disk_free_space($this->getTempDir());
        $dt = disk_total_space($this->getTempDir());
        $du = $dt - $df;
        $dp = ($du / $dt) * 100;

        $check  = new DiskUsage($dp + 1, $dp + 2, $this->getTempDir());
        $result = $check->check();

        self::assertInstanceof(SuccessInterface::class, $result);
        self::assertSame($dp, $result->getData());

        $check  = new DiskUsage($dp - 1, 100, $this->getTempDir());
        $result = $check->check();

        self::assertInstanceof(WarningInterface::class, $result);
        self::assertSame($dp, $result->getData());

        $check  = new DiskUsage(0, $dp - 1, $this->getTempDir());
        $result = $check->check();

        self::assertInstanceof(FailureInterface::class, $result);
        self::assertSame($dp, $result->getData());
    }

    public function invalidArgumentProvider(): array
    {
        return [
            ['Not an integer.', 'Not an integer.', $this->getTempDir()],
            [5, 'Not an integer.', $this->getTempDir()],
            ['Not an integer.', 100, $this->getTempDir()],
            [5, 100, []],
            [-10, 100, $this->getTempDir()],
            [105, 100, $this->getTempDir()],
            [10, -10, $this->getTempDir()],
            [10, 105, $this->getTempDir()],
        ];
    }

    protected function getTempDir(): string
    {
        // try to retrieve tmp dir
        $tmp = sys_get_temp_dir();

        // make sure there is any space there
        if (! $tmp || ! is_writable($tmp) || ! disk_free_space($tmp)) {
            self::markTestSkipped(
                'Cannot find a writable temporary directory with free disk space for Check\DiskUsage tests'
            );
        }

        return $tmp;
    }
}
