<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Traversable;

use function count;
use function extension_loaded;
use function get_class;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function phpversion;

/**
 * Validate that a named extension or a collection of extensions is available.
 */
class ExtensionLoaded extends AbstractCheck implements CheckInterface
{
    /** @var array|Traversable */
    protected $extensions;

    /** @var bool */
    protected $autoload = true;

    /**
     * @param  string|array|Traversable  $extensionName PHP extension name or an array of names
     * @throws InvalidArgumentException
     */
    public function __construct($extensionName)
    {
        if (is_object($extensionName) && ! $extensionName instanceof Traversable) {
            throw new InvalidArgumentException(
                'Expected a module name (string) , an array or Traversable of strings, got ' . get_class($extensionName)
            );
        }

        if (! is_object($extensionName) && ! is_array($extensionName) && ! is_string($extensionName)) {
            throw new InvalidArgumentException('Expected a module name (string) or an array of strings');
        }

        if (is_string($extensionName)) {
            $this->extensions = [$extensionName];
        } else {
            $this->extensions = $extensionName;
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
        $missing = [];
        foreach ($this->extensions as $ext) {
            if (! extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        if (count($missing)) {
            if (count($missing) > 1) {
                return new Failure('Extensions ' . implode(', ', $missing) . ' are not available.', $missing);
            } else {
                return new Failure('Extension ' . implode('', $missing) . ' is not available.', $missing);
            }
        } else {
            if (count($this->extensions) > 1) {
                $versions = [];
                foreach ($this->extensions as $ext) {
                    $versions[$ext] = phpversion($ext) ? : 'loaded';
                }

                return new Success(
                    implode(',', $this->extensions) . ' extensions are loaded.',
                    $versions
                );
            } else {
                $ext = $this->extensions[0];

                return new Success(
                    $ext . ' extension is loaded.',
                    $ext . ' ' . (phpversion($ext) ? phpversion($ext) : 'loaded')
                );
            }
        }
    }
}
