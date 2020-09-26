<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\ExecutedMigration;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

class DoctrineMigration extends AbstractCheck
{
    /**
     * @var DependencyFactory
     */
    private $dependencyFactory;

    public function __construct(DependencyFactory $dependencyFactory)
    {
        $this->dependencyFactory = $dependencyFactory;
    }

    /**
     * Perform the actual check and return a ResultInterface
     *
     * @return ResultInterface
     */
    public function check()
    {
        $allMigrations = $this->dependencyFactory->getMigrationRepository()->getMigrations();
        $executedMigrations = $this->dependencyFactory->getMetadataStorage()->getExecutedMigrations();

        $availableVersions = array_map(static function (AvailableMigration $availableMigration) {
            return $availableMigration->getVersion();
        }, $allMigrations->getItems());

        $migratedVersions = array_map(static function (ExecutedMigration $executedMigration) {
            return $executedMigration->getVersion();
        }, $executedMigrations->getItems());

        $notMigratedVersions = array_diff($availableVersions, $migratedVersions);
        if (! empty($notMigratedVersions)) {
            return new Failure('Not all migrations applied', $notMigratedVersions);
        }

        $notAvailableVersion = array_diff($migratedVersions, $availableVersions);
        if (! empty($notAvailableVersion)) {
            return new Failure('Migrations applied which are not available', $notMigratedVersions);
        }

        return new Success();
    }
}
