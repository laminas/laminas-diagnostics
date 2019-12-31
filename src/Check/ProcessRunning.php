<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;

/**
 * Check if a process with given name or ID is currently running.
 */
class ProcessRunning extends AbstractCheck
{
    /**
     * @var string
     */
    private $processName;

    /**
     * @var int
     */
    private $pid;

    /**
     * @param string|int $processNameOrPid   Name or ID of the process to find.
     * @throws \InvalidArgumentException
     */
    public function __construct($processNameOrPid)
    {
        if (empty($processNameOrPid)) {
            throw new InvalidArgumentException(sprintf(
                'Wrong argument provided for ProcessRunning check - ' .
                'expected a process name (string) or pid (positive number).',
                gettype($processNameOrPid)
            ));
        }

        if (! is_numeric($processNameOrPid) && ! is_scalar($processNameOrPid)) {
            throw new InvalidArgumentException(sprintf(
                'Wrong argument provided for ProcessRunning check - ' .
                'expected a process name (string) or pid (positive number) but got %s',
                gettype($processNameOrPid)
            ));
        }

        if (is_numeric($processNameOrPid)) {
            if ((int) $processNameOrPid < 0) {
                throw new InvalidArgumentException(sprintf(
                    'Wrong argument provided for ProcessRunning check - ' .
                    'expected pid to be a positive number but got %s',
                    (int) $processNameOrPid
                ));
            }
            $this->pid = (int) $processNameOrPid;
        } else {
            $this->processName = $processNameOrPid;
        }
    }

    /**
     * @see Laminas\Diagnostics\CheckInterface::check()
     */
    public function check()
    {
        // TODO: make more OS agnostic
        if ($this->pid) {
            return $this->checkAgainstPid();
        }

        return $this->checkAgainstProcessName();
    }

    /**
     * @return \Laminas\Diagnostics\Result\ResultInterface
     */
    private function checkAgainstPid()
    {
        exec('ps -p ' . (int) $this->pid, $output, $return);

        if ($return == 1) {
            return new Failure(sprintf('Process with PID %s is not currently running.', $this->pid));
        }

        return new Success();
    }

    /**
     * @return \Laminas\Diagnostics\Result\ResultInterface
     */
    private function checkAgainstProcessName()
    {
        exec('ps -efww | grep ' . escapeshellarg($this->processName) . ' | grep -v grep', $output, $return);

        if ($return > 0) {
            return new Failure(sprintf('Could not find any running process containing "%s"', $this->processName));
        }

        return new Success();
    }
}
