<?php

namespace LaminasTest\Diagnostics;

use InvalidArgumentException;
use Laminas\Diagnostics\Check\ApcFragmentation;
use PHPUnit\Framework\TestCase;

/** @covers \Laminas\Diagnostics\Check\ApcFragmentation */
final class ApcFragmentationTest extends TestCase
{
    /**
     * @dataProvider invalidArgumentProvider
     * @param int|string $warningThreshold
     * @param int|string $criticalThreshold
     */
    public function testInvalidArguments($warningThreshold, $criticalThreshold): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ApcFragmentation($warningThreshold, $criticalThreshold);
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
}
