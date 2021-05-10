<?php

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
