<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

/**
 * Checks if a JSON file is available and valid
 */
class JsonFile extends AbstractFileCheck
{
    /**
     * @param string $file
     * @return ResultInterface
     */
    protected function validateFile($file)
    {
        if (is_null(json_decode(file_get_contents($file)))) {
            return new Failure(sprintf('Could no decode JSON file "%s"', $file));
        }

        return new Success();
    }
}
