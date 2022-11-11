<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;

use function trigger_error;

final class TriggerUserError extends AbstractCheck
{
    /** @var ?string */
    protected $label = '';

    private string $message;

    private int $severity;

    private bool $result = true;

    public function __construct(string $message, int $severity, bool $result = true)
    {
        $this->message  = $message;
        $this->severity = $severity;
        $this->result   = $result;
    }

    /** @return bool */
    public function check()
    {
        trigger_error($this->message, $this->severity);

        return $this->result;
    }
}
