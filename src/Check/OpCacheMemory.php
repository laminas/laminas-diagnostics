<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

use function array_key_exists;
use function assert;
use function function_exists;
use function is_array;
use function opcache_get_status;

/**
 * Checks to see if the OpCache memory usage is below warning/critical thresholds
 */
class OpCacheMemory extends AbstractMemoryCheck
{
    /**
     * OpCache information
     */
    private array|bool $opCacheInfo = false;

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()     *
     *
     * @return Failure|Skip|Success|Warning
     */
    public function check()
    {
        if (! function_exists('opcache_get_status')) {
            return new Warning('Laminas OPcache extension is not available');
        }

        $this->opCacheInfo = opcache_get_status(false);

        if (! is_array($this->opCacheInfo) || ! array_key_exists('memory_usage', $this->opCacheInfo)) {
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
        assert(is_array($this->opCacheInfo));

        return $this->opCacheInfo['memory_usage']['used_memory']
            + $this->opCacheInfo['memory_usage']['free_memory']
            + $this->opCacheInfo['memory_usage']['wasted_memory'];
    }

    /**
     * Returns the used memory in bytes
     *
     * @return int
     */
    protected function getUsedMemory()
    {
        assert(is_array($this->opCacheInfo));

        return $this->opCacheInfo['memory_usage']['used_memory'];
    }
}
