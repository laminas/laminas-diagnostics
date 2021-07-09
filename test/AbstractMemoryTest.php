<?php

namespace LaminasTest\Diagnostics;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class AbstractMemoryTest extends TestCase
{
    /**
     * @dataProvider invalidArgumentProvider
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
            [10, 105]
        ];
    }

    abstract protected function createCheck($warningThreshold, $criticalThreshold);
}
