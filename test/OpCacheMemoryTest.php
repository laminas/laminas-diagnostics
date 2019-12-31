<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\OpCacheMemory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class OpCacheMemoryTest extends AbstractMemoryTest
{
    protected function createCheck($warningThreshold, $criticalThreshold)
    {
        return new OpCacheMemory($warningThreshold, $criticalThreshold);
    }
}
