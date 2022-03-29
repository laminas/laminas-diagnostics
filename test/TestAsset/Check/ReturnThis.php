<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;

class ReturnThis extends AbstractCheck
{
    /** @var mixed */
    protected $value;

    /** @param mixed $value */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /** @return mixed */
    public function check()
    {
        return $this->value;
    }
}
