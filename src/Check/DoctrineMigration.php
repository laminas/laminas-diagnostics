<?php

namespace Laminas\Diagnostics\Check;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\ExecutedMigration;
use Doctrine\Migrations\Version\Version;
use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use LogicException;

class DoctrineMigration extends AbstractCheck
{
    /**
     * Type depends on the installed version of doctrine/migrations:
     * for ^2.0 the input is a Configuration instance,
     * for ^3.0 the input is a DependencyFactory instance
     *
     * @var DependencyFactory|Configuration
     */
    private $input;

    public function __construct($input)
    {
        if (! $input instanceof DependencyFactory && ! $input instanceof Configuration) {
            throw new InvalidArgumentException(<<<'MESSAGE'
                Invalid Argument for DoctrineMigration check.
                If you are using doctrine/migrations ^3.0,
                pass Doctrine\Migrations\DependencyFactory as argument.
                If you are using doctrine/migrations ^2.0,
                pass Doctrine\Migrations\Configuration\Configuration as argument.
                MESSAGE
            );
        }

        $this->input = $input;
    }

    /**
     * Perform the actual check and return a ResultInterface
     */
    public function check(): ResultInterface
    {
        $availableVersions = $this->getAvailableVersions();
        $migratedVersions = $this->getMigratedVersions();

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

    /**
     * @return Version[]
     */
    private function getAvailableVersions(): array
    {
        if ($this->input instanceof DependencyFactory) {
            return $this->getAvailableVersionsFromDependencyFactory($this->input);
        }

        if ($this->input instanceof Configuration) {
            return $this->input->getAvailableVersions();
        }

        throw new LogicException(<<<'MESSAGE'
            Unexpected class for $input detected. Could not check for Doctrine Migrations.
            MESSAGE
        );
    }

    /**
     * @return Version[]
     */
    private function getMigratedVersions(): array
    {
        if ($this->input instanceof DependencyFactory) {
            return $this->getMigratedVersionsFromDependencyFactory($this->input);
        }

        if ($this->input instanceof Configuration) {
            return $this->input->getMigratedVersions();
        }

        throw new LogicException(<<<'MESSAGE'
            Unexpected class for $input detected. Could not check for Doctrine Migrations.
            MESSAGE
        );
    }

    /**
     * @return Version[]
     */
    private function getAvailableVersionsFromDependencyFactory(DependencyFactory $dependencyFactory): array
    {
        $allMigrations = $dependencyFactory->getMigrationRepository()->getMigrations();

        return array_map(static function (AvailableMigration $availableMigration) {
            return $availableMigration->getVersion();
        }, $allMigrations->getItems());
    }

    /**
     * @return Version[]
     */
    private function getMigratedVersionsFromDependencyFactory(DependencyFactory $dependencyFactory): array
    {
        $executedMigrations = $dependencyFactory->getMetadataStorage()->getExecutedMigrations();

        return array_map(static function (ExecutedMigration $executedMigration) {
            return $executedMigration->getVersion();
        }, $executedMigrations->getItems());
    }
}
