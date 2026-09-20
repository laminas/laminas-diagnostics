<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;
use Override;

final class AlwaysFailure extends AbstractCheck
{
    /** @return Failure */
    #[Override]
    public function check()
    {
        return new Failure('This check always results in failure!');
    }
}
