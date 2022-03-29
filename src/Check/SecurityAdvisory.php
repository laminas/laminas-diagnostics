<?php

namespace Laminas\Diagnostics\Check;

use Enlightn\SecurityChecker\AdvisoryAnalyzer;
use Enlightn\SecurityChecker\AdvisoryFetcher;
use Enlightn\SecurityChecker\AdvisoryParser;
use Enlightn\SecurityChecker\Composer;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

use function class_exists;
use function count;
use function file_exists;
use function getcwd;
use function gettype;
use function is_file;
use function is_readable;
use function is_scalar;
use function sprintf;

use const DIRECTORY_SEPARATOR;

/**
 * Checks installed composer dependencies against the SensioLabs Security Advisory database.
 */
class SecurityAdvisory extends AbstractCheck
{
    /** @var string */
    protected $lockFilePath;

    /** @var AdvisoryAnalyzer|null */
    protected $advisoryAnalyzer;

    /**
     * @param  string $lockFilePath Path to composer.lock
     * @throws InvalidArgumentException
     */
    public function __construct($lockFilePath = null)
    {
        if (! class_exists(AdvisoryAnalyzer::class)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to find "%s" class. Please install "%s" library to use this Check.',
                AdvisoryAnalyzer::class,
                'enlightn/security-checker'
            ));
        }

        if (! $lockFilePath) {
            if (! file_exists('composer.lock')) {
                throw new InvalidArgumentException(
                    'You have not provided lock file path and there is no "composer.lock" file in current directory.'
                );
            }

            $lockFilePath = getcwd() . DIRECTORY_SEPARATOR . 'composer.lock';
        } elseif (! is_scalar($lockFilePath)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid argument 2 provided for SecurityAdvisory check - expected file name (string) , got %s',
                gettype($lockFilePath)
            ));
        }

        $this->lockFilePath = $lockFilePath;
    }

    /**
     * @return ResultInterface
     * @throws GuzzleException
     */
    public function check()
    {
        if ($this->advisoryAnalyzer === null) {
            $advisoriesDirectory = (new AdvisoryFetcher())->fetchAdvisories();
            $parser              = new AdvisoryParser($advisoriesDirectory);

            $this->advisoryAnalyzer = new AdvisoryAnalyzer($parser->getAdvisories());
        }

        try {
            if (! file_exists($this->lockFilePath) || ! is_file($this->lockFilePath)) {
                return new Failure(sprintf(
                    'Cannot find composer lock file at %s',
                    $this->lockFilePath
                ), $this->lockFilePath);
            }

            if (! is_readable($this->lockFilePath)) {
                return new Failure(sprintf(
                    'Cannot open composer lock file at %s',
                    $this->lockFilePath
                ), $this->lockFilePath);
            }

            $dependencies = (new Composer())->getDependencies($this->lockFilePath);

            $advisories = $this->advisoryAnalyzer->analyzeDependencies($dependencies);

            if (! empty($advisories)) {
                return new Failure(sprintf(
                    'Found security advisories for %u composer package(s)',
                    count($advisories)
                ), $advisories);
            }
        } catch (Exception $e) {
            return new Warning($e->getMessage());
        }

        return new Success(sprintf(
            'There are currently no security advisories for packages specified in %s',
            $this->lockFilePath
        ));
    }
}
