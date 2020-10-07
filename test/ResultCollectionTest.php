<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics;

use ArrayObject;
use InvalidArgumentException;
use Laminas\Diagnostics\Result\Collection;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use LaminasTest\Diagnostics\TestAsset\Check\AlwaysSuccess;
use LaminasTest\Diagnostics\TestAsset\Result\Unknown;
use PHPUnit\Framework\TestCase;
use stdClass;

class ResultCollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    protected function setUp() : void
    {
        $this->collection = new Collection();
    }

    protected function tearDown() : void
    {
    }

    public static function invalidKeysProvider()
    {
        return [
            [0],
            [1],
            ['foo'],
            [new stdClass],
            [new ArrayObject],
            [new Success()],
        ];
    }

    public static function invalidValuesProvider()
    {
        return [
            [0],
            [1],
            ['foo'],
            [new stdClass],
            [new ArrayObject],
            [new AlwaysSuccess()],
        ];
    }

    public function testClassCapabilities()
    {
        $this->assertInstanceOf('Traversable', $this->collection);
        $this->assertInstanceOf('Iterator', $this->collection);
    }

    public function testBasicTypesData()
    {
        $test = new Success('foo', 'bar');
        $this->assertEquals('foo', $test->getMessage());
        $this->assertEquals('bar', $test->getData());

        $test = new Warning('foo', 'bar');
        $this->assertEquals('foo', $test->getMessage());
        $this->assertEquals('bar', $test->getData());

        $test = new Failure('foo', 'bar');
        $this->assertEquals('foo', $test->getMessage());
        $this->assertEquals('bar', $test->getData());

        $test = new Unknown('foo', 'bar');
        $this->assertEquals('foo', $test->getMessage());
        $this->assertEquals('bar', $test->getData());
    }

    public function testBasicGettingAndSetting()
    {
        $test = new AlwaysSuccess();
        $result = new Success();

        $this->collection[$test] = $result;
        $this->assertSame($result, $this->collection[$test]);

        unset($this->collection[$test]);
        $this->assertFalse($this->collection->offsetExists($test));
    }

    /**
     * @dataProvider invalidKeysProvider
     */
    public function testInvalidKeySet($key)
    {
        $result = new Success();

        $this->expectException(InvalidArgumentException::class);
        $this->collection[$key] = $result;
    }

    /**
     * @dataProvider invalidKeysProvider
     */
    public function testInvalidKeyGet($key)
    {
        $result = new Success();

        $this->expectException(InvalidArgumentException::class);
        $this->collection[$key];
    }

    /**
     * @dataProvider invalidKeysProvider
     */
    public function testInvalidKeyUnset($key)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->collection->offsetUnset($key);
    }

    /**
     * @dataProvider invalidKeysProvider
     */
    public function testInvalidKeyExists($key)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->collection->offsetExists($key);
    }

    /**
     * @dataProvider invalidValuesProvider
     */
    public function testInvalidValuesSet($value)
    {
        $key = new AlwaysSuccess();

        $this->expectException(InvalidArgumentException::class);
        $this->collection[$key] = $value;
    }

    public function testCounters()
    {
        $this->assertEquals(0, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(0, $this->collection->getFailureCount());
        $this->assertEquals(0, $this->collection->getUnknownCount());

        $success1 = new Success();
        $test1 = new AlwaysSuccess();
        $this->collection[$test1] = $success1;
        $this->assertEquals(1, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(0, $this->collection->getFailureCount());
        $this->assertEquals(0, $this->collection->getUnknownCount());

        $success2 = new Success();
        $test2 = new AlwaysSuccess();
        $this->collection[$test2] = $success2;
        $this->assertEquals(2, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(0, $this->collection->getFailureCount());
        $this->assertEquals(0, $this->collection->getUnknownCount());

        $failure1 = new Failure();
        $test3 = new AlwaysSuccess();
        $this->collection[$test3] = $failure1;
        $this->assertEquals(2, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(1, $this->collection->getFailureCount());
        $this->assertEquals(0, $this->collection->getUnknownCount());

        $warning1 = new Warning();
        $test4 = new AlwaysSuccess();
        $this->collection[$test4] = $warning1;
        $this->assertEquals(2, $this->collection->getSuccessCount());
        $this->assertEquals(1, $this->collection->getWarningCount());
        $this->assertEquals(1, $this->collection->getFailureCount());
        $this->assertEquals(0, $this->collection->getUnknownCount());

        $unknown = new Unknown();
        $test5 = new AlwaysSuccess();
        $this->collection[$test5] = $unknown;
        $this->assertEquals(2, $this->collection->getSuccessCount());
        $this->assertEquals(1, $this->collection->getWarningCount());
        $this->assertEquals(1, $this->collection->getFailureCount());
        $this->assertEquals(1, $this->collection->getUnknownCount());

        $failure2 = new Failure();
        $this->collection[$test2] = $failure2;
        $this->assertEquals(1, $this->collection->getSuccessCount());
        $this->assertEquals(1, $this->collection->getWarningCount());
        $this->assertEquals(2, $this->collection->getFailureCount());
        $this->assertEquals(1, $this->collection->getUnknownCount());

        unset($this->collection[$test4]);
        $this->assertEquals(1, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(2, $this->collection->getFailureCount());
        $this->assertEquals(1, $this->collection->getUnknownCount());

        unset($this->collection[$test2]);
        $this->assertEquals(1, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(1, $this->collection->getFailureCount());
        $this->assertEquals(1, $this->collection->getUnknownCount());

        unset($this->collection[$test5]);
        $this->assertEquals(1, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(1, $this->collection->getFailureCount());
        $this->assertEquals(0, $this->collection->getUnknownCount());

        $this->collection[$test1] = $unknown;
        $this->assertEquals(0, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(1, $this->collection->getFailureCount());
        $this->assertEquals(1, $this->collection->getUnknownCount());

        $this->collection[$test3] = $warning1;
        $this->assertEquals(0, $this->collection->getSuccessCount());
        $this->assertEquals(1, $this->collection->getWarningCount());
        $this->assertEquals(0, $this->collection->getFailureCount());
        $this->assertEquals(1, $this->collection->getUnknownCount());

        $this->collection[$test3] = $success1;
        $this->assertEquals(1, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(0, $this->collection->getFailureCount());
        $this->assertEquals(1, $this->collection->getUnknownCount());

        $this->collection[$test1] = $success2;
        $this->assertEquals(2, $this->collection->getSuccessCount());
        $this->assertEquals(0, $this->collection->getWarningCount());
        $this->assertEquals(0, $this->collection->getFailureCount());
        $this->assertEquals(0, $this->collection->getUnknownCount());
    }

    public function testIteration()
    {
        $tests = $results = [];
        $test = $result = null;

        for ($x = 0; $x < 10; $x++) {
            $test     = new AlwaysSuccess();
            $result   = new Success();
            $tests[]  = $test;
            $results[] = $result;
            $this->collection[$test] = $result;
        }

        $x = 0;
        $this->collection->rewind();
        foreach ($this->collection as $test) {
            $this->assertSame($tests[$x], $test);
            $this->assertSame($results[$x], $this->collection[$test]);
            $x++;
        }
    }
}
