<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

use function is_file;
use function is_readable;
use function is_string;
use function sprintf;

/**
 * Abstract class for handling different file checks
 */
abstract class AbstractFileCheck extends AbstractCheck
{
    /** @var iterable<string> */
    protected $files;

    /**
     * @param  iterable<string>|string $files Path name or an array / Traversable of paths
     * @throws InvalidArgumentException
     */
    public function __construct(iterable|string $files)
    {
        if (is_string($files)) {
            $this->files = [$files];
        } else {
            $this->files = $files;
        }
    }

    /**
     * @return ResultInterface
     */
    public function check()
    {
        foreach ($this->files as $file) {
            if (! is_file($file) || ! is_readable($file)) {
                return new Failure(sprintf('File "%s" does not exist or is not readable!', $file));
            }

            if (($validationResult = $this->validateFile($file)) instanceof FailureInterface) {
                return $validationResult;
            }
        }

        return new Success('All files are available and valid');
    }

    /**
     * Validates a specific file type and returns a check result
     *
     * @param string $file
     * @return ResultInterface
     */
    abstract protected function validateFile($file);
}
