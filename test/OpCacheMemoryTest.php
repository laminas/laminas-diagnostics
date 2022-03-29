<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\OpCacheMemory;

class OpCacheMemoryTest extends AbstractMemoryTest
{
    /**
     * @param int|string $warningThreshold
     * @param int|string $criticalThreshold
     * @return OpCacheMemory
     */
    protected function createCheck($warningThreshold, $criticalThreshold)
    {
        return new OpCacheMemory($warningThreshold, $criticalThreshold);
    }
}
