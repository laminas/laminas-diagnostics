<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */
namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Success;

class TriggerWarning extends AbstractCheck
{
    public function check()
    {
        strpos(); // <-- this will throw a real warning

        return new Success(); // this should be ignored
    }
}
