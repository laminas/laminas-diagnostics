<?php

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
