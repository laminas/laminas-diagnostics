<?php

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

/** @covers \Laminas\Diagnostics\Result\Collection */
final class ResultCollectionTest extends TestCase
{
    private Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new Collection();
    }

    public static function invalidKeysProvider(): array
    {
        return [
            [0],
            [1],
            ['foo'],
            [new stdClass()],
            [new ArrayObject()],
            [new Success()],
        ];
    }

    public static function invalidValuesProvider(): array
    {
        return [
            [0],
            [1],
            ['foo'],
            [new stdClass()],
            [new ArrayObject()],
            [new AlwaysSuccess()],
        ];
    }

    public function testClassCapabilities(): void
    {
        self::assertInstanceOf('Traversable', $this->collection);
        self::assertInstanceOf('Iterator', $this->collection);
    }

    public function testBasicTypesData(): void
    {
        $test = new Success('foo', 'bar');

        self::assertSame('foo', $test->getMessage());
        self::assertSame('bar', $test->getData());

        $test = new Warning('foo', 'bar');

        self::assertSame('foo', $test->getMessage());
        self::assertSame('bar', $test->getData());

        $test = new Failure('foo', 'bar');

        self::assertSame('foo', $test->getMessage());
        self::assertSame('bar', $test->getData());

        $test = new Unknown('foo', 'bar');

        self::assertSame('foo', $test->getMessage());
        self::assertSame('bar', $test->getData());
    }

    public function testBasicGettingAndSetting(): void
    {
        $test   = new AlwaysSuccess();
        $result = new Success();

        $this->collection[$test] = $result;

        self::assertSame($result, $this->collection[$test]);

        unset($this->collection[$test]);

        self::assertFalse($this->collection->offsetExists($test));
    }

    /**
     * @dataProvider invalidKeysProvider
     */
    public function testInvalidKeySet(mixed $key): void
    {
        $result = new Success();

        $this->expectException(InvalidArgumentException::class);

        $this->collection[$key] = $result;
    }

    /**
     * @dataProvider invalidKeysProvider
     */
    public function testInvalidKeyGet(mixed $key): void
    {
        new Success();

        $this->expectException(InvalidArgumentException::class);

        $this->collection[$key];
    }

    /**
     * @dataProvider invalidKeysProvider
     */
    public function testInvalidKeyUnset(mixed $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->collection->offsetUnset($key);
    }

    /**
     * @dataProvider invalidKeysProvider
     */
    public function testInvalidKeyExists(mixed $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->collection->offsetExists($key);
    }

    /**
     * @dataProvider invalidValuesProvider
     */
    public function testInvalidValuesSet(mixed $value): void
    {
        $key = new AlwaysSuccess();

        $this->expectException(InvalidArgumentException::class);

        $this->collection[$key] = $value;
    }

    public function testCounters(): void
    {
        self::assertSame(0, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(0, $this->collection->getFailureCount());
        self::assertSame(0, $this->collection->getUnknownCount());

        $success1                 = new Success();
        $test1                    = new AlwaysSuccess();
        $this->collection[$test1] = $success1;

        self::assertSame(1, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(0, $this->collection->getFailureCount());
        self::assertSame(0, $this->collection->getUnknownCount());

        $success2                 = new Success();
        $test2                    = new AlwaysSuccess();
        $this->collection[$test2] = $success2;

        self::assertSame(2, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(0, $this->collection->getFailureCount());
        self::assertSame(0, $this->collection->getUnknownCount());

        $failure1                 = new Failure();
        $test3                    = new AlwaysSuccess();
        $this->collection[$test3] = $failure1;

        self::assertSame(2, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(1, $this->collection->getFailureCount());
        self::assertSame(0, $this->collection->getUnknownCount());

        $warning1                 = new Warning();
        $test4                    = new AlwaysSuccess();
        $this->collection[$test4] = $warning1;

        self::assertSame(2, $this->collection->getSuccessCount());
        self::assertSame(1, $this->collection->getWarningCount());
        self::assertSame(1, $this->collection->getFailureCount());
        self::assertSame(0, $this->collection->getUnknownCount());

        $unknown                  = new Unknown();
        $test5                    = new AlwaysSuccess();
        $this->collection[$test5] = $unknown;

        self::assertSame(2, $this->collection->getSuccessCount());
        self::assertSame(1, $this->collection->getWarningCount());
        self::assertSame(1, $this->collection->getFailureCount());
        self::assertSame(1, $this->collection->getUnknownCount());

        $failure2                 = new Failure();
        $this->collection[$test2] = $failure2;

        self::assertSame(1, $this->collection->getSuccessCount());
        self::assertSame(1, $this->collection->getWarningCount());
        self::assertSame(2, $this->collection->getFailureCount());
        self::assertSame(1, $this->collection->getUnknownCount());

        unset($this->collection[$test4]);

        self::assertSame(1, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(2, $this->collection->getFailureCount());
        self::assertSame(1, $this->collection->getUnknownCount());

        unset($this->collection[$test2]);

        self::assertSame(1, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(1, $this->collection->getFailureCount());
        self::assertSame(1, $this->collection->getUnknownCount());

        unset($this->collection[$test5]);

        self::assertSame(1, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(1, $this->collection->getFailureCount());
        self::assertSame(0, $this->collection->getUnknownCount());

        $this->collection[$test1] = $unknown;

        self::assertSame(0, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(1, $this->collection->getFailureCount());
        self::assertSame(1, $this->collection->getUnknownCount());

        $this->collection[$test3] = $warning1;

        self::assertSame(0, $this->collection->getSuccessCount());
        self::assertSame(1, $this->collection->getWarningCount());
        self::assertSame(0, $this->collection->getFailureCount());
        self::assertSame(1, $this->collection->getUnknownCount());

        $this->collection[$test3] = $success1;

        self::assertSame(1, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(0, $this->collection->getFailureCount());
        self::assertSame(1, $this->collection->getUnknownCount());

        $this->collection[$test1] = $success2;

        self::assertSame(2, $this->collection->getSuccessCount());
        self::assertSame(0, $this->collection->getWarningCount());
        self::assertSame(0, $this->collection->getFailureCount());
        self::assertSame(0, $this->collection->getUnknownCount());
    }

    public function testIteration(): void
    {
        $tests = $results = [];
        $test  = $result = null;

        for ($x = 0; $x < 10; $x++) {
            $test                    = new AlwaysSuccess();
            $result                  = new Success();
            $tests[]                 = $test;
            $results[]               = $result;
            $this->collection[$test] = $result;
        }

        $x = 0;
        $this->collection->rewind();
        foreach ($this->collection as $test) {
            self::assertSame($tests[$x], $test);
            self::assertSame($results[$x], $this->collection[$test]);

            $x++;
        }
    }
}
