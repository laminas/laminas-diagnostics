<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;

use function call_user_func_array;

/**
 * Run a callback function and return result.
 */
class Callback extends AbstractCheck implements CheckInterface
{
    /** @var callable */
    protected $callback;

    /** @var array */
    protected $params = [];

    /** @throws InvalidArgumentException */
    public function __construct(callable $callback, array $params = [])
    {
        $this->callback = $callback;
        $this->params   = $params;
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
