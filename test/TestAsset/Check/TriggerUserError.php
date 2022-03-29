<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;

use function trigger_error;

class TriggerUserError extends AbstractCheck
{
    /** @var string */
    protected $label = '';

    /** @var string */
    protected $message;

    /** @var int */
    protected $severity;

    /** @var bool */
    protected $result = true;

    /**
     * @param string $message
     * @param int $severity
     * @param bool $result
     */
    public function __construct($message, $severity, $result = true)
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
