<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Traversable;

interface CheckCollectionInterface
{
    /**
     * Return a list of CheckInterface's.
     *
     * @return array|Traversable
     */
    public function getChecks();
}
