<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

use function disk_free_space;
use function disk_total_space;
use function is_numeric;
use function sprintf;

/**
 * Checks to see if the disk usage is below warning/critical percent thresholds
 */
class DiskUsage extends AbstractCheck implements CheckInterface
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
     * The disk path to check.
     *
     * @var string
     */
    protected $path;

    /**
     * @param numeric $warningThreshold  A number between 0 and 100
     * @param numeric $criticalThreshold A number between 0 and 100
     * @param string  $path              The disk path to check, i.e. '/tmp' or 'C:' (defaults to /)
     * @throws InvalidArgumentException
     */
    public function __construct(
        int|float|string $warningThreshold,
        int|float|string $criticalThreshold,
        string $path = '/'
    ) {
        if (! is_numeric($warningThreshold)) {
            throw new InvalidArgumentException(
                'Invalid warningThreshold argument - expecting an integer'
            );
        }

        if (! is_numeric($criticalThreshold)) {
            throw new InvalidArgumentException(
                'Invalid criticalThreshold argument - expecting an integer'
            );
        }

        if ($warningThreshold > 100 || $warningThreshold < 0) {
            throw new InvalidArgumentException(
                'Invalid warningThreshold argument - expecting an integer between 1 and 100'
            );
        }

        if ($criticalThreshold > 100 || $criticalThreshold < 0) {
            throw new InvalidArgumentException(
                'Invalid criticalThreshold argument - expecting an integer between 1 and 100'
            );
        }

        $this->warningThreshold  = (int) $warningThreshold;
        $this->criticalThreshold = (int) $criticalThreshold;
        $this->path              = $path;
    }

    /**
     * Perform the check
     *
     * @return Failure|Success|Warning
     */
    public function check()
    {
        $df = disk_free_space($this->path);
        $dt = disk_total_space($this->path);
        $du = $dt - $df;
        $dp = ($du / $dt) * 100;

        if ($dp >= $this->criticalThreshold) {
            return new Failure(sprintf('Disk usage too high: %2d percent.', $dp), $dp);
        }

        if ($dp >= $this->warningThreshold) {
            return new Warning(sprintf('Disk usage high: %2d percent.', $dp), $dp);
        }

        return new Success(sprintf('Disk usage is %2d percent.', $dp), $dp);
    }
}
