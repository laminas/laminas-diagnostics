<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\ApcMemory;

class ApcMemoryTest extends AbstractMemoryTest
{
    /**
     * @param int|string $warningThreshold
     * @param int|string $criticalThreshold
     * @return ApcMemory
     */
    protected function createCheck($warningThreshold, $criticalThreshold)
    {
        return new ApcMemory($warningThreshold, $criticalThreshold);
    }
}
