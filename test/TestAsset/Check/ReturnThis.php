<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Override;

final class ReturnThis extends AbstractCheck
{
    public function __construct(protected mixed $value)
    {
    }

    /** @return mixed */
    #[Override]
    public function check()
    {
        return $this->value;
    }
}
