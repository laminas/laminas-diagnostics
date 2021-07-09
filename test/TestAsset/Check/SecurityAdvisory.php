<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

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
