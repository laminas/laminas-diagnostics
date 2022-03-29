<?php

namespace LaminasTest\Diagnostics\TestAsset\Reporter;

use ArrayObject;
use Laminas\Diagnostics\Check\CheckInterface as Check;
use Laminas\Diagnostics\Result\Collection as ResultsResult;
use Laminas\Diagnostics\Result\ResultInterface as Result;
use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;

abstract class AbstractReporter implements ReporterInterface
{
    /** @param array $runnerConfig */
    public function onStart(ArrayObject $checks, $runnerConfig)
    {
    }

    /** @param string|null $checkAlias */
    public function onBeforeRun(Check $check, $checkAlias = null)
    {
    }

    /** @param string|null $checkAlias */
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
