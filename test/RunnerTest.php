<?php

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

use function is_string;

use const E_USER_ERROR;
use const E_WARNING;
use const PHP_MAJOR_VERSION;

/** @covers \Laminas\Diagnostics\Runner\Runner */
final class RunnerTest extends TestCase
{
    private Runner $runner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runner = new Runner();
    }

    public function checksAndResultsProvider(): array
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
                Success::class,
            ],
            [
                false,
                Failure::class,
            ],
            [
                null,
                Failure::class,
            ],
            [
                new stdClass(),
                Failure::class,
            ],
            [
                'abc',
                Warning::class,
            ],
        ];
    }

    public function testConfig(): void
    {
        self::assertFalse($this->runner->getBreakOnFailure());
        self::assertIsNumeric($this->runner->getCatchErrorSeverity());

        $this->runner->setConfig([
            'break_on_failure'     => true,
            'catch_error_severity' => 100,
        ]);

        self::assertTrue($this->runner->getBreakOnFailure());
        self::assertSame(100, $this->runner->getCatchErrorSeverity());

        $this->runner->setBreakOnFailure(false);
        $this->runner->setCatchErrorSeverity(200);

        self::assertFalse($this->runner->getBreakOnFailure());
        self::assertSame(200, $this->runner->getCatchErrorSeverity());

        $this->runner = new Runner([
            'break_on_failure'     => true,
            'catch_error_severity' => 300,
        ]);

        self::assertTrue($this->runner->getBreakOnFailure());
        self::assertSame(300, $this->runner->getCatchErrorSeverity());
        self::assertSame([
            'break_on_failure'     => true,
            'catch_error_severity' => 300,
        ], $this->runner->getConfig());
    }

    public function testInvalidValueForSetConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->runner->setConfig(10);
    }

    public function testUnknownValueInConfig(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->runner->setConfig([
            'foo' => 'bar',
        ]);
    }

    public function testManagingChecks(): void
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $check3 = new AlwaysSuccess();
        $this->runner->addCheck($check1);
        $this->runner->addChecks([
            $check2,
            $check3,
        ]);

        self::assertContains($check1, $this->runner->getChecks());
        self::assertContains($check2, $this->runner->getChecks());
        self::assertContains($check3, $this->runner->getChecks());
    }

    public function testManagingChecksWithAliases(): void
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $check3 = new AlwaysSuccess();
        $this->runner->addCheck($check1, 'foo');
        $this->runner->addCheck($check2, 'bar');

        self::assertSame($check1, $this->runner->getCheck('foo'));
        self::assertSame($check2, $this->runner->getCheck('bar'));

        $this->runner->addChecks([
            'baz' => $check3,
        ]);

        self::assertSame($check3, $this->runner->getCheck('baz'));
    }

    public function testGetNonExistentAliasThrowsException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->runner->getCheck('non-existent-check');
    }

    public function testConstructionWithChecks(): void
    {
        $check1       = new AlwaysSuccess();
        $check2       = new AlwaysSuccess();
        $this->runner = new Runner([], [$check1, $check2]);

        self::assertCount(2, $this->runner->getChecks());
        self::assertContains($check1, $this->runner->getChecks());
        self::assertContains($check2, $this->runner->getChecks());
    }

    public function testConstructionWithReporter(): void
    {
        $reporter     = $this->createMock(AbstractReporter::class);
        $this->runner = new Runner([], [], $reporter);

        self::assertCount(1, $this->runner->getReporters());
        self::assertContains($reporter, $this->runner->getReporters());
    }

    public function testAddInvalidCheck(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->runner->addChecks([new stdClass()]);
    }

    public function testAddWrongParam(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->runner->addChecks('foo');
    }

    public function testAddReporter(): void
    {
        $reporter = new BasicConsole();
        $this->runner->addReporter($reporter);

        self::assertContains($reporter, $this->runner->getReporters());
    }

    public function testRemoveReporter(): void
    {
        $reporter1 = new BasicConsole();
        $reporter2 = new BasicConsole();
        $this->runner->addReporter($reporter1);
        $this->runner->addReporter($reporter2);

        self::assertContains($reporter1, $this->runner->getReporters());
        self::assertContains($reporter2, $this->runner->getReporters());

        $this->runner->removeReporter($reporter1);

        self::assertNotContains($reporter1, $this->runner->getReporters());
        self::assertContains($reporter2, $this->runner->getReporters());
    }

    public function testStart(): void
    {
        $this->runner->addCheck(new AlwaysSuccess());

        $reporter = $this->createMock(AbstractReporter::class);
        $reporter
            ->expects(self::once())
            ->method('onStart')
            ->with(self::isInstanceOf(ArrayObject::class), self::isType('array'));

        $this->runner->addReporter($reporter);
        $this->runner->run();
    }

    public function testBeforeRun(): void
    {
        $check = new AlwaysSuccess();
        $this->runner->addCheck($check);

        $reporter = $this->createMock(AbstractReporter::class);
        $reporter
            ->expects(self::once())
            ->method('onBeforeRun')
            ->with(self::identicalTo($check));

        $this->runner->addReporter($reporter);
        $this->runner->run();
    }

    public function testAfterRun(): void
    {
        $check = new AlwaysSuccess();
        $this->runner->addCheck($check);

        $reporter = $this->createMock(AbstractReporter::class);
        $reporter
            ->expects(self::once())
            ->method('onAfterRun')
            ->with(self::identicalTo($check));

        $this->runner->addReporter($reporter);
        $this->runner->run();
    }

    public function testAliasIsKeptAfterRun(): void
    {
        $checkAlias = 'foo';
        $check      = new AlwaysSuccess();
        $this->runner->addCheck($check, $checkAlias);

        $reporter = $this->createMock(AbstractReporter::class);
        $reporter
            ->expects(self::once())
            ->method('onAfterRun')
            ->with(self::identicalTo($check), $check->check(), $checkAlias);

        $this->runner->addReporter($reporter);
        $this->runner->run($checkAlias);
    }

    /**
     * @dataProvider checksAndResultsProvider
     * @param mixed $value
     * @param mixed $expectedResult
     */
    public function testStandardResults($value, $expectedResult): void
    {
        $check = new ReturnThis($value);
        $this->runner->addCheck($check);
        $results = $this->runner->run();

        if (is_string($expectedResult)) {
            self::assertInstanceOf($expectedResult, $results[$check]);
        } else {
            self::assertSame($expectedResult, $results[$check]);
        }
    }

    public function testGetLastResult(): void
    {
        $this->runner->addCheck(new AlwaysSuccess());
        $result = $this->runner->run();

        self::assertInstanceOf(Collection::class, $result);
        self::assertSame($result, $this->runner->getLastResults());
    }

    public function testExceptionResultsInFailure(): void
    {
        $exception = new Exception();
        $check     = new ThrowException($exception);
        $this->runner->addCheck($check);
        $results = $this->runner->run();

        self::assertInstanceOf(Failure::class, $results[$check]);
    }

    public function testPHPWarningResultsInFailure(): void
    {
        if (PHP_MAJOR_VERSION >= 8) {
            self::markTestSkipped('Test case raises a TypeError under PHP 8, instead of a warning');
        }

        $check = new TriggerWarning();
        $this->runner->addCheck($check);
        $results = $this->runner->run();

        self::assertInstanceOf(Failure::class, $results[$check]);
        self::assertInstanceOf(ErrorException::class, $results[$check]->getData());
        self::assertSame(E_WARNING, $results[$check]->getData()->getSeverity());
    }

    public function testPHPUserErrorResultsInFailure(): void
    {
        $check = new TriggerUserError('error', E_USER_ERROR);
        $this->runner->addCheck($check);
        $results = $this->runner->run();

        self::assertInstanceOf(Failure::class, $results[$check]);
        self::assertInstanceOf(ErrorException::class, $results[$check]->getData());
        self::assertSame(E_USER_ERROR, $results[$check]->getData()->getSeverity());
    }

    public function testBreakOnFirstFailure(): void
    {
        $check1 = new AlwaysFailure();
        $check2 = new AlwaysSuccess();
        $this->runner->addCheck($check1);
        $this->runner->addCheck($check2);
        $this->runner->setBreakOnFailure(true);

        $results = $this->runner->run();

        self::assertInstanceOf(Collection::class, $results);
        self::assertSame(1, $results->count());
        self::assertFalse($results->offsetExists($check2));
        self::assertInstanceOf(FailureInterface::class, $results->offsetGet($check1));
    }

    public function testBeforeRunSkipTest(): void
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $this->runner->addCheck($check1);
        $this->runner->addCheck($check2);

        $reporter = $this->createMock(AbstractReporter::class);
        $reporter
            ->expects(self::atLeastOnce())
            ->method('onBeforeRun')
            ->with(self::isInstanceOf(CheckInterface::class))
            ->will(self::onConsecutiveCalls(
                false,
                true
            ));
        $this->runner->addReporter($reporter);

        $results = $this->runner->run();

        self::assertInstanceOf(Collection::class, $results);
        self::assertSame(1, $results->count());
        self::assertFalse($results->offsetExists($check1));
        self::assertInstanceOf(SuccessInterface::class, $results->offsetGet($check2));
    }

    public function testAfterRunStopTesting(): void
    {
        $check1 = new AlwaysSuccess();
        $check2 = new AlwaysSuccess();
        $this->runner->addCheck($check1);
        $this->runner->addCheck($check2);

        $reporter = $this->createMock(AbstractReporter::class);
        $reporter
            ->expects(self::atLeastOnce())
            ->method('onAfterRun')
            ->with(self::isInstanceOf(CheckInterface::class))
            ->will(self::onConsecutiveCalls(
                false,
                true
            ));
        $this->runner->addReporter($reporter);

        $results = $this->runner->run();

        self::assertInstanceOf(Collection::class, $results);
        self::assertSame(1, $results->count());
        self::assertFalse($results->offsetExists($check2));
        self::assertInstanceOf(SuccessInterface::class, $results->offsetGet($check1));
    }
}
