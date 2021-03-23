<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Exception;
use Laminas\Diagnostics\Result\ResultInterface;

interface CheckInterface
{
    /**
     * Perform the actual check and return a ResultInterface
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function check();

    /**
     * Return a label describing this test instance.
     *
     * @return string
     */
    public function getLabel();
}
