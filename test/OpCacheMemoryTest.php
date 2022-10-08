<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\OpCacheMemory;

/** @covers \Laminas\Diagnostics\Check\OpCacheMemory */
final class OpCacheMemoryTest extends AbstractMemoryTest
{
    /** {@inheritDoc} */
    protected function createCheck($warningThreshold, $criticalThreshold): OpCacheMemory
    {
        return new OpCacheMemory($warningThreshold, $criticalThreshold);
    }
}
