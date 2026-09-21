<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Exception;
use Laminas\Diagnostics\Check\AbstractCheck;
use Override;

final class ThrowException extends AbstractCheck
{
    #[Override]
    public function check()
    {
        throw new Exception('This check always throws a generic \Exception');
    }
}
