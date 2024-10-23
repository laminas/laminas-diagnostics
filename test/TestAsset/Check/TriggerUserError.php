<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;

use function trigger_error;

final class TriggerUserError extends AbstractCheck
{
    /** @var ?string */
    protected $label = '';

    public function __construct(private string $message, private int $severity, private bool $result = true)
    {
    }

    public function check()
    {
        throw new \ErrorException($this->message);
    }
}
