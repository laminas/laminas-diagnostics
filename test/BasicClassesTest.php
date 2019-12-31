<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

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
    public function testCoreClassTree()
    {
        foreach ([
            CheckInterface::class,
            SuccessInterface::class,
            FailureInterface::class,
            WarningInterface::class,
        ] as $class) {
            $this->assertTrue(interface_exists($class, true), 'Class "' . $class . '" exists.');
        }

        foreach ([
            AbstractCheck::class,
            AbstractResult::class,
            Success::class,
            Failure::class,
            Warning::class,
        ] as $class) {
            $this->assertTrue(class_exists($class, true), 'Class "' . $class . '" exists.');
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
            $this->assertTrue($reflection->implementsInterface(ResultInterface::class));
        }
    }

    public function testConstructor()
    {
        $result = new Success('foo', 'bar');
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertSame('foo', $result->getMessage());
        $this->assertSame('bar', $result->getData());

        $result = new Failure('foo', 'bar');
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertSame('foo', $result->getMessage());
        $this->assertSame('bar', $result->getData());

        $result = new Warning('foo', 'bar');
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertSame('foo', $result->getMessage());
        $this->assertSame('bar', $result->getData());
    }

    public function testSetters()
    {
        $result = new Success();
        $this->assertSame('', $result->getMessage());
        $this->assertSame(null, $result->getData());

        $result->setMessage('foo');
        $result->setData('bar');
        $this->assertSame('foo', $result->getMessage());
        $this->assertSame('bar', $result->getData());
    }

    public function testSimpleCheck()
    {
        $alwaysSuccess = new AlwaysSuccess();
        $this->assertNotNull($alwaysSuccess->getLabel());
        $this->assertSame($alwaysSuccess->getName(), $alwaysSuccess->getLabel());
        $this->assertSame('Always Success', trim($alwaysSuccess->getLabel()), 'Class-deferred label');

        $alwaysSuccess->setLabel('foobar');
        $this->assertSame('foobar', $alwaysSuccess->getName(), 'Explicitly set label');
        $this->assertSame($alwaysSuccess->getName(), $alwaysSuccess->getLabel());

        $result = $alwaysSuccess->check();
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertNotNull($result->getMessage());
    }
}
