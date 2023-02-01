<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Traversable;

use function count;
use function current;
use function implode;
use function is_array;
use function is_dir;
use function is_object;
use function is_string;
use function is_writable;
use function sprintf;
use function trim;

/**
 * Validate that a given path (or a collection of paths) is a dir and is writable
 */
class DirWritable extends AbstractCheck implements CheckInterface
{
    /** @var array|Traversable */
    protected $dir;

    /**
     * @param  string|array|Traversable $path Path name or an array of paths
     * @throws InvalidArgumentException
     */
    public function __construct($path)
    {
        if (is_object($path) && ! $path instanceof Traversable) {
            throw new InvalidArgumentException(
                'Expected a dir name (string), an array or Traversable of strings, got ' . $path::class
            );
        }

        if (! is_object($path) && ! is_array($path) && ! is_string($path)) {
            throw new InvalidArgumentException('Expected a dir name (string) or an array of strings');
        }

        if (is_string($path)) {
            $this->dir = [$path];
        } else {
            $this->dir = $path;
        }
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     *
     * @return Failure|Success
     */
    public function check()
    {
        $nonDirs = $unwritable = [];

        // Check each path if it's a dir and is writable
        foreach ($this->dir as $dir) {
            if (! is_dir($dir)) {
                $nonDirs[] = $dir;
            }

            if (! is_writable($dir)) {
                $unwritable[] = $dir;
            }
        }

        // Construct failure message
        $failureString = '';
        if (count($nonDirs) > 1) {
            $failureString .= sprintf('The following paths are not valid directories: %s. ', implode(', ', $nonDirs));
        } elseif (count($nonDirs) === 1) {
            $failureString .= sprintf('%s is not a valid directory. ', current($nonDirs));
        }

        if (count($unwritable) > 1) {
            $failureString .= sprintf('The following directories are not writable: %s. ', implode(', ', $unwritable));
        } elseif (count($unwritable) === 1) {
            $failureString .= sprintf('%s directory is not writable. ', current($unwritable));
        }

        // Return success or failure
        if ($failureString) {
            return new Failure(trim($failureString), ['nonDirs' => $nonDirs, 'unwritable' => $unwritable]);
        } else {
            return new Success(
                count($this->dir) > 1 ? 'All paths are writable directories.' : 'The path is a writable directory.',
                $this->dir
            );
        }
    }
}
