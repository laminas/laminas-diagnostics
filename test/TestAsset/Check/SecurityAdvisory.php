<?php

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\SecurityAdvisory as BaseCheck;
use Enlightn\SecurityChecker\AdvisoryAnalyzer;
use Enlightn\SecurityChecker\AdvisoryFetcher;

class SecurityAdvisory extends BaseCheck
{
    /**
     * @param \Enlightn\SecurityChecker\AdvisoryAnalyzer $advisoryAnalyzer
     */
    public function setAdvisoryAnalyzer(AdvisoryAnalyzer $advisoryAnalyzer): void
    {
        $this->advisoryAnalyzer = $advisoryAnalyzer;
    }

    /**
     * @param \Enlightn\SecurityChecker\AdvisoryFetcher $advisoryFetcher
     */
    public function setAdvisoryFetcher(AdvisoryFetcher $advisoryFetcher): void
    {
        $this->advisoryFetcher = $advisoryFetcher;
    }


}
