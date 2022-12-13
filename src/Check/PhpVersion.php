<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;

use function in_array;
use function is_string;
use function sprintf;
use function version_compare;

use const PHP_VERSION;

/**
 * Validate PHP version.
 *
 * This test accepts a single version and an operator or an array of
 * versions to test for.
 *
 * @psalm-type Operator = '<'|'lt'|'<='|'le'|'>'|'gt'|'>='|'ge'|'=='|'='|'eq'|'!='|'<>'|'ne'
 */
class PhpVersion extends AbstractCheck implements CheckInterface
{
    private const VALID_OPERATORS = [
        '<',
        'lt',
        '<=',
        'le',
        '>',
        'gt',
        '>=',
        'ge',
        '==',
        '=',
        'eq',
        '!=',
        '<>',
        'ne',
    ];

    /** @var iterable<array-key, string> */
    protected $version;

    /** @var string */
    protected $operator = '>=';

    /**
     * @param  string|iterable<array-key, string> $expectedVersion The expected version
     * @param  value-of<self::VALID_OPERATORS>    $operator
     * @throws InvalidArgumentException
     */
    public function __construct(string|iterable $expectedVersion, string $operator = '>=')
    {
        $this->version = is_string($expectedVersion)
            ? [$expectedVersion]
            : $expectedVersion;

        if (
            ! in_array($operator, self::VALID_OPERATORS)
        ) {
            throw new InvalidArgumentException(
                'Unknown comparison operator ' . $operator
            );
        }

        $this->operator = $operator;
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
        foreach ($this->version as $version) {
            if (! version_compare(PHP_VERSION, $version, $this->operator)) {
                return new Failure(sprintf(
                    'Current PHP version is %s, expected %s %s',
                    PHP_VERSION,
                    $this->operator,
                    $version
                ), PHP_VERSION);
            }
        }

        return new Success('Current PHP version is ' . PHP_VERSION, PHP_VERSION);
    }
}
