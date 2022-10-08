<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Success;

use function strpos;

final class TriggerWarning extends AbstractCheck
{
    /** @return Success */
    public function check()
    {
        strpos(); // <-- this will throw a real warning

        return new Success(); // this should be ignored
    }
}
