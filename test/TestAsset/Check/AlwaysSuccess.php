<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Success;
use Override;

final class AlwaysSuccess extends AbstractCheck
{
    /** @return Success */
    #[Override]
    public function check()
    {
        return new Success('This check always results in success!');
    }
}
