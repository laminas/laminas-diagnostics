<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Success;

final class AlwaysSuccess extends AbstractCheck
{
    /** @return Success */
    public function check()
    {
        return new Success('This check always results in success!');
    }
}
