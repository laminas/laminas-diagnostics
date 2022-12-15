<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;

use function class_exists;
use function count;
use function current;
use function implode;
use function is_string;

/**
 * Validate that a class or a collection of classes is available.
 */
class ClassExists extends AbstractCheck implements CheckInterface
{
    /**
     * An array of classes to check
     *
     * @var iterable<string>
     */
    protected $classes;

    /**
     * Use autoloader when looking for classes? (defaults to true)
     *
     * @var bool
     */
    protected $autoload = true;

    /**
     * @param  string|iterable<string> $classNames Class name or an array of classes
     * @param  bool                    $autoload   Use autoloader when looking for classes? (defaults to true)
     * @throws InvalidArgumentException
     */
    public function __construct(string|iterable $classNames, $autoload = true)
    {
        if (is_string($classNames)) {
            $this->classes = [$classNames];
        } else {
            $this->classes = $classNames;
        }

        $this->autoload = $autoload;
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()
     *
     * @return Success|Failure
     */
    public function check()
    {
        $missing = [];
        foreach ($this->classes as $class) {
            if (! class_exists($class, $this->autoload)) {
                $missing[] = $class;
            }
        }

        if (count($missing) > 1) {
            return new Failure('The following classes are missing: ' . implode(', ', $missing), $missing);
        } elseif (count($missing) === 1) {
            return new Failure('Class ' . current($missing) . ' does not exist', $missing);
        } else {
            return new Success('All classes are present.', $this->classes);
        }
    }
}
