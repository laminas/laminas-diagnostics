<?php
namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use LaminasTest\Diagnostics\TestAsset\Check\AlwaysSuccess;

class BasicClassesTest extends \PHPUnit_Framework_TestCase
{
    public function testCoreClassTree()
    {
        foreach(array(
            'Laminas\Diagnostics\Check\CheckInterface',
            'Laminas\Diagnostics\Result\SuccessInterface',
            'Laminas\Diagnostics\Result\FailureInterface',
            'Laminas\Diagnostics\Result\WarningInterface',
        ) as $class){
            $this->assertTrue(interface_exists($class, true), 'Class "' . $class . '" exists.');
        }

        foreach(array(
            'Laminas\Diagnostics\Check\AbstractCheck',
            'Laminas\Diagnostics\Result\AbstractResult',
            'Laminas\Diagnostics\Result\Success',
            'Laminas\Diagnostics\Result\Failure',
            'Laminas\Diagnostics\Result\Warning',
        ) as $class){
            $this->assertTrue(class_exists($class, true), 'Class "' . $class . '" exists.');
        }
        foreach(array(
            'Laminas\Diagnostics\Result\Success',
            'Laminas\Diagnostics\Result\Failure',
            'Laminas\Diagnostics\Result\Warning',
            'Laminas\Diagnostics\Result\SuccessInterface',
            'Laminas\Diagnostics\Result\FailureInterface',
            'Laminas\Diagnostics\Result\WarningInterface',
        ) as $class){
            $reflection = new \ReflectionClass($class);
            $this->assertTrue($reflection->implementsInterface('Laminas\Diagnostics\Result\ResultInterface'));
        }
    }

    public function testConstructor()
    {
        $result = new Success('foo', 'bar');
        $this->assertInstanceOf('Laminas\Diagnostics\Result\ResultInterface', $result);
        $this->assertSame('foo', $result->getMessage());
        $this->assertSame('bar', $result->getData());

        $result = new Failure('foo', 'bar');
        $this->assertInstanceOf('Laminas\Diagnostics\Result\ResultInterface', $result);
        $this->assertSame('foo', $result->getMessage());
        $this->assertSame('bar', $result->getData());

        $result = new Warning('foo', 'bar');
        $this->assertInstanceOf('Laminas\Diagnostics\Result\ResultInterface', $result);
        $this->assertSame('foo', $result->getMessage());
        $this->assertSame('bar', $result->getData());
    }

    public function testSetters()
    {
        $result = new Success();
        $this->assertSame(null, $result->getMessage());
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
        $this->assertInstanceOf('Laminas\Diagnostics\Result\ResultInterface', $result);
        $this->assertNotNull($result->getMessage());
    }
}
