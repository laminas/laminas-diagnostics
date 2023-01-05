<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Traversable;

use function count;
use function gettype;
use function implode;
use function ini_get;
use function is_array;
use function is_object;
use function is_scalar;
use function iterator_to_array;

/**
 * Make sure given PHP flag is turned on or off in php.ini
 *
 * This test accepts a string or array of strings for php flags
 */
class PhpFlag extends AbstractCheck implements CheckInterface
{
    /** @var array */
    protected $settings;

    /** @var bool */
    protected $expectedValue;

    /**
     * @param string|array|Traversable $settingName   PHP setting names to check.
     * @param bool                     $expectedValue true or false
     * @throws InvalidArgumentException
     */
    public function __construct($settingName, $expectedValue)
    {
        if (is_object($settingName)) {
            if (! $settingName instanceof Traversable) {
                throw new InvalidArgumentException(
                    'Expected setting name as string, array or traversable, got ' . $settingName::class
                );
            }
            $this->settings = iterator_to_array($settingName);
        } elseif (! is_scalar($settingName)) {
            if (! is_array($settingName)) {
                throw new InvalidArgumentException(
                    'Expected setting name as string, array or traversable, got ' . gettype($settingName)
                );
            }
            $this->settings = $settingName;
        } else {
            $this->settings = [$settingName];
        }

        if (! is_scalar($expectedValue)) {
            throw new InvalidArgumentException(
                'Expected expected value, expected boolean, got ' . gettype($expectedValue)
            );
        }

        $this->expectedValue = (bool) $expectedValue;
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
