<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\AbstractResult;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\Warning;
use Laminas\Diagnostics\Result\WarningInterface;
use LaminasTest\Diagnostics\TestAsset\Check\AlwaysSuccess;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BasicClassesTest extends TestCase
{
    public function testCoreClassTree(): void
    {
        foreach ([
            CheckInterface::class,
            SuccessInterface::class,
            FailureInterface::class,
            WarningInterface::class,
        ] as $class) {
            self::assertTrue(interface_exists($class, true), 'Class "' . $class . '" exists.');
        }

        foreach ([
            AbstractCheck::class,
            AbstractResult::class,
            Success::class,
            Failure::class,
            Warning::class,
        ] as $class) {
            self::assertTrue(class_exists($class, true), 'Class "' . $class . '" exists.');
        }
        foreach ([
            Success::class,
            Failure::class,
            Warning::class,
            SuccessInterface::class,
            FailureInterface::class,
            WarningInterface::class,
        ] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->implementsInterface(ResultInterface::class));
        }
    }

    public function testConstructor(): void
    {
        $result = new Success('foo', 'bar');
        self::assertInstanceOf(ResultInterface::class, $result);
        self::assertSame('foo', $result->getMessage());
        self::assertSame('bar', $result->getData());

        $result = new Failure('foo', 'bar');
        self::assertInstanceOf(ResultInterface::class, $result);
        self::assertSame('foo', $result->getMessage());
        self::assertSame('bar', $result->getData());

        $result = new Warning('foo', 'bar');
        self::assertInstanceOf(ResultInterface::class, $result);
        self::assertSame('foo', $result->getMessage());
        self::assertSame('bar', $result->getData());
    }

    public function testSetters(): void
    {
        $result = new Success();
        self::assertSame('', $result->getMessage());
        self::assertNull($result->getData());

        $result->setMessage('foo');
        $result->setData('bar');
        self::assertSame('foo', $result->getMessage());
        self::assertSame('bar', $result->getData());
    }

    public function testSimpleCheck(): void
    {
        $alwaysSuccess = new AlwaysSuccess();
        self::assertNotNull($alwaysSuccess->getLabel());
        self::assertSame($alwaysSuccess->getName(), $alwaysSuccess->getLabel());
        self::assertSame('Always Success', trim($alwaysSuccess->getLabel()), 'Class-deferred label');

        $alwaysSuccess->setLabel('foobar');
        self::assertSame('foobar', $alwaysSuccess->getName(), 'Explicitly set label');
        self::assertSame($alwaysSuccess->getName(), $alwaysSuccess->getLabel());

        $result = $alwaysSuccess->check();
        self::assertInstanceOf(ResultInterface::class, $result);
        self::assertNotNull($result->getMessage());
    }
}
