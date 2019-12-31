<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics\TestAsset\Reporter;

use ArrayObject;
use Laminas\Diagnostics\Check\CheckInterface as Check;
use Laminas\Diagnostics\Result\Collection as ResultsResult;
use Laminas\Diagnostics\Result\ResultInterface as Result;
use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;

abstract class AbstractReporter implements ReporterInterface
{
    public function onStart(ArrayObject $checks, $runnerConfig)
    {
    }

    public function onBeforeRun(Check $check, $checkAlias = null)
    {
    }

    public function onAfterRun(Check $check, Result $result, $checkAlias = null)
    {
    }

    public function onStop(ResultsResult $results)
    {
    }

    public function onFinish(ResultsResult $results)
    {
    }
}
