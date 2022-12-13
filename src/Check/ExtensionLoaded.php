<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;

use function count;
use function extension_loaded;
use function implode;
use function is_string;
use function phpversion;

/**
 * Validate that a named extension or a collection of extensions is available.
 */
class ExtensionLoaded extends AbstractCheck implements CheckInterface
{
    /** @var non-empty-list<string> */
    protected $extensions;

    /** @var bool */
    protected $autoload = true;

    /**
     * @param  string|non-empty-list<string>  $extensionName PHP extension name or an array of names
     * @throws InvalidArgumentException
     */
    public function __construct(string|array $extensionName)
    {
        if (is_string($extensionName)) {
            $this->extensions = [$extensionName];

            return;
        }

        $this->extensions = $extensionName;
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
