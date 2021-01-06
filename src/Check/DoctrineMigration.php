<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\ExecutedMigration;
use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

class DoctrineMigration extends AbstractCheck
{
    /**
     * @var array
     */
    private $availableVersions;

    /**
     * @var array
     */
    private $migratedVersions;

    public function __construct($input)
    {
        if ($input instanceof DependencyFactory) {
            $this->availableVersions = $this->getAvailableVersionsFromDependencyFactory($input);
            $this->migratedVersions = $this->getMigratedVersionsFromDependencyFactory($input);
            return;
        }
        
        if ($input instanceof Doctrine\Migrations\Configuration\Configuration
            && method_exists($input, 'getAvailableVersions')
            && method_exists($input, 'getMigratedVersions')
        ) {
            $this->availableVersions = $input->getAvailableVersions();
            $this->migratedVersions = $input->getMigratedVersions();
            return;
        }

        // phpcs:disable Generic.Files.LineLength.MaxExceeded,Generic.Files.LineLength.TooLong
        throw new InvalidArgumentException(
            'Invalid Argument for DoctrineMigration check.' . PHP_EOL
            . 'If you are using doctrine/migrations ^3.0, pass the Doctrine\Migrations\DependencyFactory as argument.' . PHP_EOL
            . 'If you are using doctrine/migrations ^2.0, pass the Doctrine\Migrations\Configuration\Configuration as argument.' . PHP_EOL
            . 'If you are using doctrine/migrations ^1.0, pass the Doctrine\DBAL\Migrations\Configuration\Configuration as argument.'
        );
        // phpcs:enable
    }

    /**
     * Perform the actual check and return a ResultInterface
     *
     * @return ResultInterface
     */
    public function check()
    {
        $notMigratedVersions = array_diff($this->availableVersions, $this->migratedVersions);
        if (! empty($notMigratedVersions)) {
            return new Failure('Not all migrations applied', $notMigratedVersions);
        }

        $notAvailableVersion = array_diff($this->migratedVersions, $this->availableVersions);
        if (! empty($notAvailableVersion)) {
            return new Failure('Migrations applied which are not available', $notMigratedVersions);
        }

        return new Success();
    }


    private function getAvailableVersionsFromDependencyFactory(DependencyFactory $dependencyFactory)
    {
        $allMigrations = $dependencyFactory->getMigrationRepository()->getMigrations();

        return array_map(static function (AvailableMigration $availableMigration) {
            return $availableMigration->getVersion();
        }, $allMigrations->getItems());
    }

    private function getMigratedVersionsFromDependencyFactory(DependencyFactory $dependencyFactory)
    {
        $executedMigrations = $dependencyFactory->getMetadataStorage()->getExecutedMigrations();

        return array_map(static function (ExecutedMigration $executedMigration) {
            return $executedMigration->getVersion();
        }, $executedMigrations->getItems());
    }
}
