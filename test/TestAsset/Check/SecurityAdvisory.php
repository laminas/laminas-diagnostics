<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\SecurityAdvisory as BaseCheck;
use Enlightn\SecurityChecker\AdvisoryAnalyzer;

class SecurityAdvisory extends BaseCheck
{
    /**
     * @param \Enlightn\SecurityChecker\AdvisoryAnalyzer $advisoryAnalyzer
     */
    public function setAdvisoryAnalyzer(AdvisoryAnalyzer $advisoryAnalyzer)
    {
        $this->advisoryAnalyzer = $advisoryAnalyzer;
    }
}
