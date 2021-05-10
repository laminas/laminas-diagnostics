<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Success;

class AlwaysSuccess extends AbstractCheck
{
    public function check()
    {
        return new Success('This check always results in success!');
    }
}
