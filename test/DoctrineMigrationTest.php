<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics;

use Doctrine\Migrations\Configuration\Configuration;
use Laminas\Diagnostics\Check\DoctrineMigration;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use PHPUnit\Framework\TestCase;

class DoctrineMigrationTest extends TestCase
{
    public function testEverythingMigrated(): void
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration
            ->expects(self::once())
            ->method('getAvailableVersions')
            ->willReturn(['Version1', 'Version2']);

        $configuration
            ->expects(self::once())
            ->method('getMigratedVersions')
            ->willReturn(['Version1', 'Version2']);

        $check = new DoctrineMigration($configuration);
        $result = $check->check();

        self::assertInstanceof(SuccessInterface::class, $result);
    }

    public function testNotAllMigrationsMigrated(): void
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration
            ->expects(self::once())
            ->method('getAvailableVersions')
            ->willReturn(['Version1', 'Version2']);

        $configuration
            ->expects(self::once())
            ->method('getMigratedVersions')
            ->willReturn(['Version1']);

        $check = new DoctrineMigration($configuration);
        $result = $check->check();

        self::assertInstanceof(FailureInterface::class, $result);
    }

    public function testNoExistingMigrationMigrated(): void
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration
            ->expects(self::once())
            ->method('getAvailableVersions')
            ->willReturn(['Version1']);

        $configuration
            ->expects(self::once())
            ->method('getMigratedVersions')
            ->willReturn(['Version1', 'Version2']);

        $check = new DoctrineMigration($configuration);
        $result = $check->check();

        self::assertInstanceof(FailureInterface::class, $result);
    }
}
