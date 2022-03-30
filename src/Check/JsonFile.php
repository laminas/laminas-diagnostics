<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

use function file_get_contents;
use function json_decode;
use function sprintf;

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
        if (null === json_decode(file_get_contents($file))) {
            return new Failure(sprintf('Could no decode JSON file "%s"', $file));
        }

        return new Success();
    }
}
