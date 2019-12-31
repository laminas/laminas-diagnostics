<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

/**
 * Abstract class for handling different memory checks
 */
abstract class AbstractMemoryCheck extends AbstractCheck implements CheckInterface
{
    /**
     * Percentage that will cause a warning.
     *
     * @var int
     */
    protected $warningThreshold;

    /**
     * Percentage that will cause a fail.
     *
     * @var int
     */
    protected $criticalThreshold;

    /**
     * @param int $warningThreshold  A number between 0 and 100
     * @param int $criticalThreshold A number between 0 and 100
     * @throws InvalidArgumentException
     */
    public function __construct($warningThreshold, $criticalThreshold)
    {
        if (!is_numeric($warningThreshold)) {
            throw new InvalidArgumentException('Invalid warningThreshold argument - expecting an integer');
        }

        if (!is_numeric($criticalThreshold)) {
            throw new InvalidArgumentException('Invalid criticalThreshold argument - expecting an integer');
        }

        if ($warningThreshold > 100 || $warningThreshold < 0) {
            throw new InvalidArgumentException('Invalid warningThreshold argument - expecting an integer between 1 and 100');
        }

        if ($criticalThreshold > 100 || $criticalThreshold < 0) {
            throw new InvalidArgumentException('Invalid criticalThreshold argument - expecting an integer between 1 and 100');
        }

        $this->warningThreshold  = (int)$warningThreshold;
        $this->criticalThreshold = (int)$criticalThreshold;
    }

    /**
     * Perform the check
     *
     * @see \Laminas\Diagnostics\Check\CheckInterface::check()     *
     * @return Failure|Skip|Success|Warning
     */
    public function check()
    {
        $percentUsed = ($this->getUsedMemory() / $this->getTotalMemory()) * 100;
        $message = sprintf('%.0f%% of available %s memory used.', $percentUsed, $this->formatBytes($this->getTotalMemory()));

        if ($percentUsed > $this->criticalThreshold) {
            return new Failure($message);
        }

        if ($percentUsed > $this->warningThreshold) {
            return new Warning($message);
        }

        return new Success($message);
    }

    /**
     * Returns the total memory in bytes
     *
     * @return int
     */
    abstract protected function getTotalMemory();

    /**
     * Returns the used memory in bytes
     *
     * @return int
     */
    abstract protected function getUsedMemory();

    /**
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $size = 'B';

        foreach (array('B', 'KB', 'MB', 'GB') as $size) {
            if ($bytes < 1024) {
                break;
            }

            $bytes /= 1024;
        }

        return sprintf("%.0f %s", $bytes, $size);
    }
}
