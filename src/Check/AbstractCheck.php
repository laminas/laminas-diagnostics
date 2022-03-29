<?php

namespace Laminas\Diagnostics\Check;

use function preg_replace;
use function strrpos;
use function substr;
use function trim;

abstract class AbstractCheck implements CheckInterface
{
    /**
     * Explicitly set label.
     *
     * @var string
     */
    protected $label;

    /**
     * Return a label describing this test instance.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->label !== null) {
            return $this->label;
        }

        $class = static::class;
        $class = substr($class, strrpos($class, '\\') + 1);
        $class = preg_replace('/([A-Z])/', ' $1', $class);

        return trim($class);
    }

    /**
     * Alias for getLabel()
     *
     * @see CheckInterface::getLabel()
     *
     * @return string
     */
    public function getName()
    {
        return $this->getLabel();
    }

    /**
     * Set a custom label for this test instance.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
}
