<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use ErrorException;
use Exception;
use Laminas\Diagnostics\Check\AbstractCheck;

final class TriggerUserError extends AbstractCheck
{
    /** @var ?string */
    protected $label = '';

    public function __construct(private string $message, private bool $result = true)
    {
    }

    public function check()
    {
        throw new ErrorException($this->message);
    }
}
