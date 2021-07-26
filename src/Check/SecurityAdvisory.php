<?php

namespace Laminas\Diagnostics\Check;

use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Enlightn\SecurityChecker\AdvisoryAnalyzer;
use Enlightn\SecurityChecker\AdvisoryFetcher;
use Enlightn\SecurityChecker\AdvisoryParser;
use Enlightn\SecurityChecker\Composer;

/**
 * Checks installed composer dependencies against the SensioLabs Security Advisory database.
 */
class SecurityAdvisory extends AbstractCheck
{
    /**
     * @var string
     */
    protected $lockFilePath;

    /**
     * @var \Enlightn\SecurityChecker\AdvisoryAnalyzer|null
     */
    protected $advisoryAnalyzer = null;

    /**
     * @param  string $lockFilePath Path to composer.lock
     * @throws InvalidArgumentException|\GuzzleHttp\Exception\GuzzleException
     */
    public function __construct($lockFilePath = null)
    {
        if (! class_exists('Enlightn\SecurityChecker\AdvisoryAnalyzer')) {
            throw new InvalidArgumentException(sprintf(
                'Unable to find "%s" class. Please install "%s" library to use this Check.',
                'Enlightn\SecurityChecker\AdvisoryAnalyzer',
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

        $this->lockFilePath    = $lockFilePath;
    }

    public function check()
    {
        if ($this->advisoryAnalyzer === null) {
            $parser = new AdvisoryParser((new AdvisoryFetcher)->fetchAdvisories());

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

            $dependencies = (new Composer)->getDependencies($this->lockFilePath);

            $advisories = $this->advisoryAnalyzer->analyzeDependencies($dependencies);

            if (! empty($advisories)) {
                return new Failure(sprintf(
                    'Found security advisories for %u composer package(s)',
                    count($advisories)
                ), $advisories);
            }
        } catch (\Exception $e) {
            return new Warning($e->getMessage());
        }

        return new Success(sprintf(
            'There are currently no security advisories for packages specified in %s',
            $this->lockFilePath
        ));
    }
}
