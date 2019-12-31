<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics\TestAsset\Check;

use Laminas\Diagnostics\Check\SecurityAdvisory as BaseCheck;
use SensioLabs\Security\SecurityChecker;

class SecurityAdvisory extends BaseCheck
{
    /**
     * @param SecurityChecker $securityChecker
     */
    public function setSecurityChecker(SecurityChecker $securityChecker)
    {
        $this->securityChecker = $securityChecker;
    }
}
