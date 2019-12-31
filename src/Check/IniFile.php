<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

/**
 * Checks if an INI file is available and valid
 */
class IniFile extends AbstractFileCheck
{
    /**
     * @param string $file
     * @return ResultInterface
     */
    protected function validateFile($file)
    {
        if (! is_array($ini = parse_ini_file($file)) or count($ini) < 1) {
            return new Failure(sprintf('Could not parse INI file "%s"!', $file));
        }

        return new Success();
    }
}
