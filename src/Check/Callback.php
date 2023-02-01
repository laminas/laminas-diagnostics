<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;

use function call_user_func_array;
use function is_callable;

/**
 * Run a callback function and return result.
 */
class Callback extends AbstractCheck implements CheckInterface
{
    /** @var callable */
    protected $callback;

    /**
     * @param  callable                  $callback
     * @param  array                     $params
     * @throws InvalidArgumentException
     */
    public function __construct($callback, protected $params = [])
    {
        if (! is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback provided; not callable');
        }

        $this->callback = $callback;
    }

    /**
     * Perform the Check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     *
     * @return mixed
     */
    public function check()
    {
        return call_user_func_array($this->callback, $this->params);
    }
}
