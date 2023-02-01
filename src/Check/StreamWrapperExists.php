<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Traversable;

use function count;
use function current;
use function implode;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use function stream_get_wrappers;

/**
 * Validate that a stream wrapper exists.
 */
class StreamWrapperExists extends AbstractCheck implements CheckInterface
{
    /** @var array|Traversable */
    protected $wrappers;

    /**
     * @param  string|array|Traversable $wrappers Stream wrapper name or an array of names
     * @throws InvalidArgumentException
     */
    public function __construct($wrappers)
    {
        if (is_object($wrappers) && ! $wrappers instanceof Traversable) {
            throw new InvalidArgumentException(sprintf(
                'Expected a stream wrapper name (string), or an array or Traversable of strings;'
                . ' received %s',
                $wrappers::class
            ));
        }

        if (! is_object($wrappers) && ! is_array($wrappers) && ! is_string($wrappers)) {
            throw new InvalidArgumentException(
                'Expected a stream wrapper name (string) or an array of strings'
            );
        }

        if (is_string($wrappers)) {
            $this->wrappers = [$wrappers];
        } else {
            $this->wrappers = $wrappers;
        }
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     *
     * @return Failure|Success
     */
    public function check()
    {
        $missingWrappers   = [];
        $availableWrappers = stream_get_wrappers();

        foreach ($this->wrappers as $class) {
            if (! in_array($class, $availableWrappers)) {
                $missingWrappers[] = $class;
            }
        }

        if (count($missingWrappers) === 1) {
            return new Failure(
                sprintf('Stream wrapper %s is not available', current($missingWrappers)),
                $availableWrappers
            );
        }

        if (count($missingWrappers)) {
            return new Failure(
                sprintf(
                    'The following stream wrappers are missing: %s',
                    implode(', ', $missingWrappers)
                ),
                $availableWrappers
            );
        }

        return new Success(
            sprintf('%s stream wrapper(s) are available', implode(', ', $this->wrappers)),
            $availableWrappers
        );
    }
}
