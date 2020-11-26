<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\MigrationsRepository;
use Laminas\Diagnostics\Check\DoctrineMigration;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use PHPUnit\Framework\TestCase;

class DoctrineMigrationTest extends TestCase
{
    /**
     * @dataProvider provideMigrationTestCases
     */
    public function testDoctrineMigrationsVersion3(
        array $availableVersions,
        array $migratedVersions,
        string $expectedResult
    ): void {
        $migrationRepository = $this->getMockBuilder(MigrationsRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $migrationRepository
            ->expects(self::once())
            ->method('getMigrations')
            ->willReturn($availableVersions);

        $metadataStorage = $this->getMockBuilder(MetadataStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadataStorage
            ->expects(self::once())
            ->method('getExecutedMigrations')
            ->willReturn($migratedVersions);

        $dependencyFactory = $this->getMockBuilder(DependencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dependencyFactory
            ->expects(self::once())
            ->method('getMigrationRepository')
            ->willReturn($migrationRepository);

        $dependencyFactory
            ->expects(self::once())
            ->method('getMetadataStorage')
            ->willReturn($metadataStorage);

        $check = new DoctrineMigration($dependencyFactory);
        $result = $check->check();

        self::assertInstanceof($expectedResult, $result);
    }

    /**
     * @dataProvider provideMigrationTestCases
     */
    public function testDoctrineMigrationsVersion2(
        array $availableVersions,
        array $migratedVersions,
        string $expectedResult
    ): void {
        $configuration = $this->getMockBuilder(Doctrine\Migrations\Configuration\Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration
            ->expects(self::once())
            ->method('getAvailableVersions')
            ->willReturn($availableVersions);

        $configuration
            ->expects(self::once())
            ->method('getMigratedVersions')
            ->willReturn($migratedVersions);

        $check = new DoctrineMigration($configuration);
        $result = $check->check();

        self::assertInstanceof($expectedResult, $result);
    }

    /**
     * @dataProvider provideMigrationTestCases
     */
    public function testDoctrineMigrationsVersion1(
        array $availableVersions,
        array $migratedVersions,
        string $expectedResult
    ): void {
        $configuration = $this->getMockBuilder(Doctrine\DBAL\Migrations\Configuration\Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration
            ->expects(self::once())
            ->method('getAvailableVersions')
            ->willReturn($availableVersions);

        $configuration
            ->expects(self::once())
            ->method('getMigratedVersions')
            ->willReturn($migratedVersions);

        $check = new DoctrineMigration($configuration);
        $result = $check->check();

        self::assertInstanceof($expectedResult, $result);
    }

    public function testThrowsExceptionForInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid Argument for DoctrineMigration check.
            If you are using doctrine/migrations ^3.0, pass the Doctrine\Migrations\DependencyFactory as argument.
            If you are using doctrine/migrations ^2.0, pass the Doctrine\Migrations\Configuration\Configuration as argument.
            If you are using doctrine/migrations ^1.0, pass the Doctrine\DBAL\Migrations\Configuration\Configuration as argument.'
        );

        new DoctrineMigration(new \stdClass());
    }

    public function provideMigrationTestCases(): iterable
    {
        yield 'everything migrated' => [
            ['Version1', 'Version2'],
            ['Version1', 'Version2'],
            SuccessInterface::class
        ];
        yield 'not all migration migrated' => [
            ['Version1', 'Version2'],
            ['Version1'],
            FailureInterface::class
        ];
        yield 'not existing migration migrated' => [
            ['Version1'],
            ['Version1', 'Version2'],
            FailureInterface::class
        ];
    }
}
