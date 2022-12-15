<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;

use function count;
use function current;
use function implode;
use function is_dir;
use function is_readable;
use function is_string;
use function trim;

/**
 * Validate that a given path (or a collection of paths) is a dir and is readable
 */
class DirReadable extends AbstractCheck implements CheckInterface
{
    /** @var array<string> */
    protected $dir;

    /**
     * @param  string|array<string> $path Path name or an array of paths
     * @throws InvalidArgumentException
     */
    public function __construct(string|array $path)
    {
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
        $nonDirs = $unreadable = [];

        // Check each path if it's a dir and is readable
        foreach ($this->dir as $dir) {
            if (! is_dir($dir)) {
                $nonDirs[] = $dir;
            }

            if (! is_readable($dir)) {
                $unreadable[] = $dir;
            }
        }

        // Construct failure message
        $failureString = '';
        if (count($nonDirs) > 1) {
            $failureString .= 'The following paths are not valid directories: ' . implode(', ', $nonDirs) . ' ';
        } elseif (count($nonDirs) === 1) {
            $failureString .= current($nonDirs) . ' is not a valid directory. ';
        }

        if (count($unreadable) > 1) {
            $failureString .= 'The following directories are not readable: ' . implode(', ', $unreadable);
        } elseif (count($unreadable) === 1) {
            $failureString .= current($unreadable) . ' directory is not readable.';
        }

        // Return success or failure
        if ($failureString) {
            return new Failure(trim($failureString), ['nonDirs' => $nonDirs, 'unreadable' => $unreadable]);
        } else {
            return new Success(
                count($this->dir) > 1 ? 'All paths are readable directories.' : 'The path is a readable directory.',
                $this->dir
            );
        }
    }
}
