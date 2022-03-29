<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;

class AlwaysFailure extends AbstractCheck
{
    /** @return Failure */
    public function check()
    {
        return new Failure('This check always results in failure!');
    }
}
