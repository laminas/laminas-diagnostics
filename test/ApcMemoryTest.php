<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\ApcMemory;
use Override;

/** @covers \Laminas\Diagnostics\Check\ApcMemory */
final class ApcMemoryTest extends AbstractMemoryTest
{
    /** {@inheritDoc} */
    #[Override]
    protected function createCheck($warningThreshold, $criticalThreshold): ApcMemory
    {
        return new ApcMemory($warningThreshold, $criticalThreshold);
    }
}
