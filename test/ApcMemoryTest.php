<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\ApcMemory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ApcMemoryTest extends AbstractMemoryTest
{
    protected function createCheck($warningThreshold, $criticalThreshold)
    {
        return new ApcMemory($warningThreshold, $criticalThreshold);
    }
}
