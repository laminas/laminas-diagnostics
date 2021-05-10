<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;

class ReturnThis extends AbstractCheck
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function check()
    {
        return $this->value;
    }
}
