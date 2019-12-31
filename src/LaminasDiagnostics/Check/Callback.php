<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;

/**
 * Run a callback function and return result.
 */
class Callback extends AbstractCheck implements CheckInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @param  callable                  $callback
     * @param  array                     $params
     * @throws \InvalidArgumentException
     */
    public function __construct($callback, $params = array())
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback provided; not callable');
        }

        $this->callback = $callback;
        $this->params = $params;
    }

    /**
     * Perform the Check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     * @return mixed
     */
    public function check()
    {
        return call_user_func_array($this->callback, $this->params);
    }
}
