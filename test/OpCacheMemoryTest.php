<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\OpCacheMemory;
use Override;

/** @covers \Laminas\Diagnostics\Check\OpCacheMemory */
final class OpCacheMemoryTest extends AbstractMemoryTest
{
    /** {@inheritDoc} */
    #[Override]
    protected function createCheck($warningThreshold, $criticalThreshold): OpCacheMemory
    {
        return new OpCacheMemory($warningThreshold, $criticalThreshold);
    }
}
