<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;

use function trigger_error;

final class TriggerUserError extends AbstractCheck
{
    /** @var string */
    protected $label = '';

    public function __construct(private string $message, private int $severity, private bool $result = true)
    {
    }

    /** @return bool */
    public function check()
    {
        trigger_error($this->message, $this->severity);

        return $this->result;
    }
}
