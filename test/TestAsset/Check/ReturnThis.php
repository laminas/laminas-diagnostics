<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;

final class ReturnThis extends AbstractCheck
{
    public function __construct(protected mixed $value)
    {
    }

    /** @return mixed */
    public function check()
    {
        return $this->value;
    }
}
