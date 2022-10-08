<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Enlightn\SecurityChecker\AdvisoryAnalyzer;
use Laminas\Diagnostics\Check\SecurityAdvisory as BaseCheck;

final class SecurityAdvisory extends BaseCheck
{
    public function setAdvisoryAnalyzer(AdvisoryAnalyzer $advisoryAnalyzer): void
    {
        $this->advisoryAnalyzer = $advisoryAnalyzer;
    }
}
