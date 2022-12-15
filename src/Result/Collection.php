<?php

namespace Laminas\Diagnostics\Result;

use InvalidArgumentException;
use Laminas\Diagnostics\Check\CheckInterface;
use ReturnTypeWillChange;
use SplObjectStorage;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

/**
 * Utility class to store Results entities for corresponding Checks
 *
 * @template-extends SplObjectStorage<CheckInterface, ResultInterface>
 */
class Collection extends SplObjectStorage
{
    /**
     * Number of successful results
     *
     * @var int
     */
    protected $countSuccess = 0;

    /**
     * Number of warnings
     *
     * @var int
     */
    protected $countWarning = 0;

    /**
     * Number of failures
     *
     * @var int
     */
    protected $countFailure = 0;

    /**
     * Number of skips
     *
     * @var int
     */
    protected $countSkip = 0;

    /**
     * Number of unrecognised results
     *
     * @var int
     */
    protected $countUnknown = 0;

    /**
     * Get number of successful Check results.
     *
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->countSuccess;
    }

    /**
     * Get number of failed Check results.
     *
     * @return int
     */
    public function getFailureCount()
    {
        return $this->countFailure;
    }

    /**
     * Get number of warnings.
     *
     * @return int
     */
    public function getWarningCount()
    {
        return $this->countWarning;
    }

    /**
     * Get number of skips.
     *
     * @return int
     */
    public function getSkipCount()
    {
        return $this->countSkip;
    }

    /**
     * Get number of unknown results.
     *
     * @return int
     */
    public function getUnknownCount()
    {
        return $this->countUnknown;
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function offsetGet($index)
    {
        $this->validateIndex($index);

        return parent::offsetGet($index);
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function offsetExists($index)
    {
        $this->validateIndex($index);

        return parent::offsetExists($index);
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function offsetSet($index, $checkResult = null)
    {
        $this->validateIndex($index);
        $this->validateValue($checkResult);

        // Decrement counters when replacing existing item
        if (parent::offsetExists($index)) {
            $this->updateCounters(parent::offsetGet($index), -1);
        }

        // Store the new instance
        parent::offsetSet($index, $checkResult);

        // Increment counters
        $this->updateCounters($checkResult, 1);
    }

    /** @inheritDoc */
    #[ReturnTypeWillChange]
    public function offsetUnset($index)
    {
        $this->validateIndex($index);

        // Decrement counters when replacing existing item
        if (parent::offsetExists($index)) {
            $this->updateCounters(parent::offsetGet($index), -1);
        }

        parent::offsetUnset($index);
    }

    /**
     * Adjust internal result counters.
     *
     * @param int             $delta
     */
    protected function updateCounters(ResultInterface $result, $delta = 1)
    {
        if ($result instanceof SuccessInterface) {
            $this->countSuccess += $delta;
        } elseif ($result instanceof FailureInterface) {
            $this->countFailure += $delta;
        } elseif ($result instanceof WarningInterface) {
            $this->countWarning += $delta;
        } elseif ($result instanceof SkipInterface) {
            $this->countSkip += $delta;
        } else {
            $this->countUnknown += $delta;
        }
    }

    /**
     * Validate index object.
     *
     * @param  mixed                    $index
     * @psalm-assert CheckInterface $index
     * @return string
     * @throws InvalidArgumentException
     */
    protected function validateIndex($index)
    {
        if (! $index instanceof CheckInterface) {
            $what = is_object($index) ? 'object of type ' . get_class($index) : gettype($index);
            throw new InvalidArgumentException(sprintf(
                'Cannot use %s as index for this collection. Expected instance of CheckInterface.',
                $what
            ));
        }

        return $index;
    }

    /**
     * Validate if the value can be stored in this collection.
     *
     * @param  mixed                    $checkResult
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function validateValue($checkResult)
    {
        if (! is_object($checkResult) || ! $checkResult instanceof ResultInterface) {
            $what = is_object($checkResult) ? 'object of type ' . get_class($checkResult) : gettype($checkResult);
            throw new InvalidArgumentException(sprintf(
                'This collection cannot hold %s. Expected instance of %s\ResultInterface',
                $what,
                __NAMESPACE__
            ));
        }

        return $checkResult;
    }
}
