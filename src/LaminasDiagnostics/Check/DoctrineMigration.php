<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

class DoctrineMigration extends AbstractCheck
{
    /**
     * @var Configuration
     */
    private $migrationConfiguration;

    /**
     * @param Configuration $migrationConfiguration
     */
    public function __construct(Configuration $migrationConfiguration)
    {
        $this->migrationConfiguration = $migrationConfiguration;
    }

    /**
     * Perform the actual check and return a ResultInterface
     *
     * @return ResultInterface
     */
    public function check()
    {
        $availableVersions = $this->migrationConfiguration->getAvailableVersions();
        $migratedVersions = $this->migrationConfiguration->getMigratedVersions();

        $notMigratedVersions = array_diff($availableVersions, $migratedVersions);
        if (!empty($notMigratedVersions)) {
            return new Failure('Not all migrations applied', $notMigratedVersions);
        }

        $notAvailableVersion = array_diff($migratedVersions, $availableVersions);
        if (!empty($notAvailableVersion)) {
            return new Failure('Migrations applied which are not available', $notMigratedVersions);
        }

        return new Success();
    }
}
