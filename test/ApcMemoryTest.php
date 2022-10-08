<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\ApcMemory;

/** @covers \Laminas\Diagnostics\Check\ApcMemory */
final class ApcMemoryTest extends AbstractMemoryTest
{
    /** {@inheritDoc} */
    protected function createCheck($warningThreshold, $criticalThreshold): ApcMemory
    {
        return new ApcMemory($warningThreshold, $criticalThreshold);
    }
}
