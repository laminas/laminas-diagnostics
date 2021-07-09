<?php

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
