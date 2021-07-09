<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Exception;
use Laminas\Diagnostics\Check\AbstractCheck;

class ThrowException extends AbstractCheck
{
    public function check()
    {
        throw new Exception('This check always throws a generic \Exception');
    }
}
