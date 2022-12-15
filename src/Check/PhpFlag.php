<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;

use function count;
use function implode;
use function ini_get;
use function is_string;

/**
 * Make sure given PHP flag is turned on or off in php.ini
 *
 * This test accepts a string or array of strings for php flags
 */
class PhpFlag extends AbstractCheck implements CheckInterface
{
    /** @var non-empty-list<string> */
    protected $settings;

    /** @var bool */
    protected $expectedValue;

    /**
     * @param string|non-empty-list<string> $settingName   PHP setting names to check.
     * @throws InvalidArgumentException
     */
    public function __construct(string|array $settingName, bool $expectedValue)
    {
        $this->expectedValue = $expectedValue;

        if (is_string($settingName)) {
            $this->settings = [$settingName];

            return;
        }

        $this->settings = $settingName;
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     *
     * @return Success|Failure
     */
    public function check()
    {
        $failures = [];

        foreach ($this->settings as $name) {
            // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedNotEqualOperator
            if (ini_get($name) != $this->expectedValue) {
                $failures[] = $name;
            }
        }

        if (count($failures) > 1) {
            return new Failure(
                implode(', ', $failures)
                . ' are expected to be '
                . ($this->expectedValue ? 'enabled' : 'disabled')
            );
        } elseif (count($failures)) {
            return new Failure(
                $failures[0]
                . ' is expected to be '
                . ($this->expectedValue ? 'enabled' : 'disabled')
            );
        }

        if (count($this->settings) > 1) {
            return new Success(
                implode(', ', $this->settings)
                . ' are all '
                . ($this->expectedValue ? 'enabled' : 'disabled')
            );
        } else {
            return new Success(
                $this->settings[0]
                . ' is '
                . ($this->expectedValue ? 'enabled' : 'disabled')
            );
        }
    }
}
