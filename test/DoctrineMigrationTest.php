<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Laminas\Diagnostics\Check\DoctrineMigration;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use PHPUnit\Framework\TestCase;

class DoctrineMigrationTest extends TestCase
{
    public function testEverythingMigrated()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration
            ->expects($this->once())
            ->method('getAvailableVersions')
            ->will($this->returnValue(['Version1', 'Version2']));

        $configuration
            ->expects($this->once())
            ->method('getMigratedVersions')
            ->will($this->returnValue(['Version1', 'Version2']));

        $check = new DoctrineMigration($configuration);
        $result = $check->check();

        $this->assertInstanceof(SuccessInterface::class, $result);
    }

    public function testNotAllMigrationsMigrated()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration
            ->expects($this->once())
            ->method('getAvailableVersions')
            ->will($this->returnValue(['Version1', 'Version2']));

        $configuration
            ->expects($this->once())
            ->method('getMigratedVersions')
            ->will($this->returnValue(['Version1']));

        $check = new DoctrineMigration($configuration);
        $result = $check->check();

        $this->assertInstanceof(FailureInterface::class, $result);
    }

    public function testNoExistingMigrationMigrated()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration
            ->expects($this->once())
            ->method('getAvailableVersions')
            ->will($this->returnValue(['Version1']));

        $configuration
            ->expects($this->once())
            ->method('getMigratedVersions')
            ->will($this->returnValue(['Version1', 'Version2']));

        $check = new DoctrineMigration($configuration);
        $result = $check->check();

        $this->assertInstanceof(FailureInterface::class, $result);
    }
}
