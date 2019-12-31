<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

/**
 * Checks to see if the OpCache memory usage is below warning/critical thresholds
 */
class OpCacheMemory extends AbstractMemoryCheck
{
    /**
     * OpCache information
     *
     * @var array
     */
    private $opCacheInfo;

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()     *
     * @return Failure|Skip|Success|Warning
     */
    public function check()
    {
        if (!function_exists('opcache_get_status')) {
            return new Warning('Laminas OPcache extension is not available');
        }

        $this->opCacheInfo = opcache_get_status(false);

        if (!is_array($this->opCacheInfo) || !array_key_exists('memory_usage', $this->opCacheInfo)) {
            return new Warning('Laminas OPcache extension is not enabled in this environment');
        }

        return parent::check();
    }

    /**
     * Return a label describing this test instance.
     *
     * @return string
     */
    public function getLabel()
    {
        return 'OPcache Memory';
    }

    /**
     * Returns the total memory in bytes
     *
     * @return int
     */
    protected function getTotalMemory()
    {
        return $this->opCacheInfo['memory_usage']['used_memory'] + $this->opCacheInfo['memory_usage']['free_memory'] + $this->opCacheInfo['memory_usage']['wasted_memory'];
    }

    /**
     * Returns the used memory in bytes
     *
     * @return int
     */
    protected function getUsedMemory()
    {
        return $this->opCacheInfo['memory_usage']['used_memory'];
    }
}
