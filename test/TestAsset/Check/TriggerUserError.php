<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use ErrorException;
use Laminas\Diagnostics\Check\AbstractCheck;
use Override;

final class TriggerUserError extends AbstractCheck
{
    /** @var ?string */
    protected $label = '';

    public function __construct(private string $message, private bool $result = true)
    {
    }

    #[Override]
    public function check()
    {
        throw new ErrorException($this->message);
    }
}
