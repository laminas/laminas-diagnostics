<?php

namespace LaminasTest\Diagnostics\TestAsset\Reporter;

use ArrayObject;
use Laminas\Diagnostics\Check\CheckInterface as Check;
use Laminas\Diagnostics\Result\Collection as ResultsResult;
use Laminas\Diagnostics\Result\ResultInterface as Result;
use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;
use Override;

abstract class AbstractReporter implements ReporterInterface
{
    /** @param array $runnerConfig */
    #[Override]
    public function onStart(ArrayObject $checks, $runnerConfig)
    {
    }

    /** @param string|null $checkAlias */
    #[Override]
    public function onBeforeRun(Check $check, $checkAlias = null)
    {
    }

    /** @param string|null $checkAlias */
    #[Override]
    public function onAfterRun(Check $check, Result $result, $checkAlias = null)
    {
    }

    #[Override]
    public function onStop(ResultsResult $results)
    {
    }

    #[Override]
    public function onFinish(ResultsResult $results)
    {
    }
}
