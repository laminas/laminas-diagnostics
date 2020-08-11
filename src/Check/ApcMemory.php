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
 * Checks to see if the APCu memory usage is below warning/critical thresholds
 *
 * APCu memory logic borrowed from APC project:
 *      https://github.com/php/pecl-caching-apc/blob/master/apc.php
 *      authors:   Ralf Becker <beckerr@php.net>, Rasmus Lerdorf <rasmus@php.net>, Ilia Alshanetsky <ilia@prohost.org>
 *      license:   The PHP License, version 3.01
 *      copyright: Copyright (c) 2006-2011 The PHP Group
 */
class ApcMemory extends AbstractMemoryCheck
{
    /**
     * APC information
     *
     * @var array
     */
    private $apcInfo;

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()     *
     * @return Failure|Skip|Success|Warning
     */
    public function check()
    {
        if (! ini_get('apc.enabled')) {
            return new Skip('APC has not been enabled or installed.');
        }

        if (php_sapi_name() == 'cli' && ! ini_get('apc.enable_cli')) {
            return new Skip('APC has not been enabled in CLI.');
        }

        if (! function_exists('apcu_sma_info')) {
            return new Warning(sprintf(
                '%s extension is not available',
                PHP_VERSION_ID < 70000 ? 'APC' : 'APCu'
            ));
        }

        if (! $this->apcInfo = apcu_sma_info()) {
            return new Warning('Unable to retrieve APC memory status information.');
        }

        return parent::check();
    }

    /**
     * Returns the total memory in bytes
     *
     * @return int
     */
    protected function getTotalMemory()
    {
        return $this->apcInfo['num_seg'] * $this->apcInfo['seg_size'];
    }

    /**
     * Returns the used memory in bytes
     *
     * @return int
     */
    protected function getUsedMemory()
    {
        return $this->getTotalMemory() - $this->apcInfo['avail_mem'];
    }
}
