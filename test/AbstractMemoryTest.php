<?php

namespace LaminasTest\Diagnostics;

use InvalidArgumentException;
use Laminas\Diagnostics\Check\CheckInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractMemoryTest extends TestCase
{
    /**
     * @dataProvider invalidArgumentProvider
     * @param string|int $warningThreshold
     * @param string|int $criticalThreshold
     */
    public function testInvalidArguments($warningThreshold, $criticalThreshold): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createCheck($warningThreshold, $criticalThreshold);
    }

    public function invalidArgumentProvider(): array
    {
        return [
            ['Not an integer.', 'Not an integer.'],
            [5, 'Not an integer.'],
            ['Not an integer.', 100],
            [-10, 100],
            [105, 100],
            [10, -10],
            [10, 105],
        ];
    }

    /**
     * @param string|int $warningThreshold
     * @param string|int $criticalThreshold
     */
    abstract protected function createCheck($warningThreshold, $criticalThreshold): CheckInterface;
}
