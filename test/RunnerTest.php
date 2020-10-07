<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics;

use ArrayObject;
use BadMethodCallException;
use ErrorException;
use Exception;
use InvalidArgumentException;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Collection;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\Warning;
use Laminas\Diagnostics\Runner\Reporter\BasicConsole;
use Laminas\Diagnostics\Runner\Runner;
use LaminasTest\Diagnostics\TestAsset\Check\AlwaysFailure;
use LaminasTest\Diagnostics\TestAsset\Check\AlwaysSuccess;
use LaminasTest\Diagnostics\TestAsset\Check\ReturnThis;
use LaminasTest\Diagnostics\TestAsset\Check\ThrowException;
use LaminasTest\Diagnostics\TestAsset\Check\TriggerUserError;
use LaminasTest\Diagnostics\TestAsset\Check\TriggerWarning;
use LaminasTest\Diagnostics\TestAsset\Reporter\AbstractReporter;
use LaminasTest\Diagnostics\TestAsset\Result\Unknown;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class RunnerTest extends TestCase
{
    /**
     * @var Runner
     */
    protected $runner;

    protected function setUp() : void
    {
        $this->runner = new Runner();
    }

    public function checksAndResultsProvider()
    {
        return [
            [
                $success = new Success(),
                $success,
            ],
            [
                $warning = new Warning(),
                $warning,
            ],
            [
                $failure = new Failure(),
                $failure,
            ],
            [
                $unknown = new Unknown(),
                $unknown,
            ],
            [
                true,
                Success::class
            ],
            [
                false,
                Failure::class
            ],
            [
                null,
                Failure::class,
            ],
            [
                new \stdClass(),
                Failure::class,
            ],
            [
                'abc',
                Warning::class,
            ],
        ];
    }

    public function testConfig()
    {
        $this->assertFalse($this->runner->getBreakOnFailure());
        $this->assertTrue(is_numeric($this->runner->getCatchErrorSeverity()));

        $this->runner->setConfig([
            'break_on_failure'     => true,
            'catch_error_severity' => 100
        ]);

        $this->assertTrue($this->runner->getBreakOnFailure());
        $this->assertSame(100, $this->runner->getCatchErrorSeverity());

        $this->runner->setBreakOnFailure(false);
        $this->runner->setCatchErrorSeverity(200);

        $this->assertFalse($this->runner->getBreakOnFailure());
        $this->assertSame(200, $this->runner->getCatchErrorSeverity());

        $this->runner = new Runner([
            'break_on_failure'     => true,
            'catch_error_severity' => 300
        ]);

        $this->assertTrue($this->runner->getBreakOnFailure());
        $this->assertSame(300, $this->runner->getCatchErrorSeverity());
        $this->assertEquals([
            'break_on_failure'     => true,
            'catch_error_severity' => 300
        ], $this->runner->getConfig());
    }

    public function testInvalidValueForSetConfig()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->runner->setConfig(10);
    }

    public function testUnknownValueInConfig()
    {
        $this->expectException(BadMethodCallException::class);
        $this->runner->setConfig([
            'foo' => 'bar'
        ]);
    }

    public function testManagingChecks()
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $check3 = new AlwaysSuccess();
        $this->runner->addCheck($check1);
        $this->runner->addChecks([
            $check2,
            $check3
        ]);
        $this->assertContains($check1, $this->runner->getChecks());
        $this->assertContains($check2, $this->runner->getChecks());
        $this->assertContains($check3, $this->runner->getChecks());
    }

    public function testManagingChecksWithAliases()
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $check3 = new AlwaysSuccess();
        $this->runner->addCheck($check1, 'foo');
        $this->runner->addCheck($check2, 'bar');
        $this->assertSame($check1, $this->runner->getCheck('foo'));
        $this->assertSame($check2, $this->runner->getCheck('bar'));

        $this->runner->addChecks([
            'baz' => $check3,
        ]);
        $this->assertSame($check3, $this->runner->getCheck('baz'));
    }

    public function testGetNonExistentAliasThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $this->runner->getCheck('non-existent-check');
    }

    public function testConstructionWithChecks()
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $this->runner = new Runner([], [$check1, $check2]);
        $this->assertEquals(2, count($this->runner->getChecks()));
        $this->assertContains($check1, $this->runner->getChecks());
        $this->assertContains($check2, $this->runner->getChecks());
    }

    public function testConstructionWithReporter()
    {
        $reporter = $this->createMock(AbstractReporter::class);
        $this->runner = new Runner([], [], $reporter);
        $this->assertEquals(1, count($this->runner->getReporters()));
        $this->assertContains($reporter, $this->runner->getReporters());
    }

    public function testAddInvalidCheck()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->runner->addChecks([new stdClass()]);
    }

    public function testAddWrongParam()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->runner->addChecks('foo');
    }

    public function testAddReporter()
    {
        $reporter = new BasicConsole();
        $this->runner->addReporter($reporter);
        $this->assertContains($reporter, $this->runner->getReporters());
    }

    public function testRemoveReporter()
    {
        $reporter1 = new BasicConsole();
        $reporter2 = new BasicConsole();
        $this->runner->addReporter($reporter1);
        $this->runner->addReporter($reporter2);
        $this->assertContains($reporter1, $this->runner->getReporters());
        $this->assertContains($reporter2, $this->runner->getReporters());
        $this->runner->removeReporter($reporter1);
        $this->assertNotContains($reporter1, $this->runner->getReporters());
        $this->assertContains($reporter2, $this->runner->getReporters());
    }

    public function testStart()
    {
        $this->runner->addCheck(new AlwaysSuccess());
        $mock = $this->createMock(AbstractReporter::class);
        $mock->expects($this
            ->once())
            ->method('onStart')
            ->with($this->isInstanceOf(ArrayObject::class), $this->isType('array'));
        $this->runner->addReporter($mock);
        $this->runner->run();
    }

    public function testBeforeRun()
    {
        $check = new AlwaysSuccess();
        $this->runner->addCheck($check);
        $mock = $this->createMock(AbstractReporter::class);
        $mock->expects($this->once())->method('onBeforeRun')->with($this->identicalTo($check));
        $this->runner->addReporter($mock);
        $this->runner->run();
    }

    public function testAfterRun()
    {
        $check = new AlwaysSuccess();
        $this->runner->addCheck($check);
        $mock = $this->createMock(AbstractReporter::class);
        $mock->expects($this->once())->method('onAfterRun')->with($this->identicalTo($check));
        $this->runner->addReporter($mock);
        $this->runner->run();
    }

    public function testAliasIsKeptAfterRun()
    {
        $checkAlias = 'foo';
        $check = new AlwaysSuccess();
        $this->runner->addCheck($check, $checkAlias);
        $mock = $this->createMock(AbstractReporter::class);
        $mock->expects($this
            ->once())
            ->method('onAfterRun')
            ->with($this->identicalTo($check), $check->check(), $checkAlias);
        $this->runner->addReporter($mock);
        $this->runner->run($checkAlias);
    }

    /**
     * @dataProvider checksAndResultsProvider
     */
    public function testStandardResults($value, $expectedResult)
    {
        $check = new ReturnThis($value);
        $this->runner->addCheck($check);
        $results = $this->runner->run();

        if (is_string($expectedResult)) {
            $this->assertInstanceOf($expectedResult, $results[$check]);
        } else {
            $this->assertSame($expectedResult, $results[$check]);
        }
    }

    public function testGetLastResult()
    {
        $this->runner->addCheck(new AlwaysSuccess());
        $result = $this->runner->run();
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($result, $this->runner->getLastResults());
    }

    public function testExceptionResultsInFailure()
    {
        $exception = new Exception();
        $check = new ThrowException($exception);
        $this->runner->addCheck($check);
        $results = $this->runner->run();
        $this->assertInstanceOf(Failure::class, $results[$check]);
    }

    public function testPHPWarningResultsInFailure()
    {
        $check = new TriggerWarning();
        $this->runner->addCheck($check);
        $results = $this->runner->run();
        $this->assertInstanceOf(Failure::class, $results[$check]);
        $this->assertInstanceOf(ErrorException::class, $results[$check]->getData());
        $this->assertEquals(E_WARNING, $results[$check]->getData()->getSeverity());
    }

    public function testPHPUserErrorResultsInFailure()
    {
        $check = new TriggerUserError('error', E_USER_ERROR);
        $this->runner->addCheck($check);
        $results = $this->runner->run();
        $this->assertInstanceOf(Failure::class, $results[$check]);
        $this->assertInstanceOf(ErrorException::class, $results[$check]->getData());
        $this->assertEquals(E_USER_ERROR, $results[$check]->getData()->getSeverity());
    }

    public function testBreakOnFirstFailure()
    {
        $check1 = new AlwaysFailure();
        $check2 = new AlwaysSuccess();
        $this->runner->addCheck($check1);
        $this->runner->addCheck($check2);
        $this->runner->setBreakOnFailure(true);

        $results = $this->runner->run();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertFalse($results->offsetExists($check2));
        $this->assertInstanceOf(FailureInterface::class, $results->offsetGet($check1));
    }

    public function testBeforeRunSkipTest()
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $this->runner->addCheck($check1);
        $this->runner->addCheck($check2);

        $mock = $this->createMock(AbstractReporter::class);
        $mock->expects($this->atLeastOnce())
            ->method('onBeforeRun')
            ->with($this->isInstanceOf(CheckInterface::class))
            ->will($this->onConsecutiveCalls(
                false,
                true
            ))
        ;
        $this->runner->addReporter($mock);

        $results = $this->runner->run();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertFalse($results->offsetExists($check1));
        $this->assertInstanceOf(SuccessInterface::class, $results->offsetGet($check2));
    }

    public function testAfterRunStopTesting()
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $this->runner->addCheck($check1);
        $this->runner->addCheck($check2);

        $mock = $this->createMock(AbstractReporter::class);
        $mock->expects($this->atLeastOnce())
            ->method('onAfterRun')
            ->with($this->isInstanceOf(CheckInterface::class))
            ->will($this->onConsecutiveCalls(
                false,
                true
            ))
        ;
        $this->runner->addReporter($mock);

        $results = $this->runner->run();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertFalse($results->offsetExists($check2));
        $this->assertInstanceOf(SuccessInterface::class, $results->offsetGet($check1));
    }
}
