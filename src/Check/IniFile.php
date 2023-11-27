<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use RuntimeException;

use function count;
use function is_array;
use function parse_ini_file;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;

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
        set_error_handler(function ($code, $message) {
            throw new RuntimeException($message, $code);
        });
        try {
            if (! is_array($ini = parse_ini_file($file)) || count($ini) < 1) {
                return new Failure(sprintf('Could not parse INI file "%s"!', $file));
            }
        } catch (RuntimeException $e) {
            return new Failure(sprintf('Could not parse INI file "%s"! %s', $file, $e->getMessage()));
        } finally {
            restore_error_handler();
        }

        return new Success();
    }
}
