<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diagnostics;

use ArrayObject;
use Laminas\Diagnostics\Result\Collection;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Laminas\Diagnostics\Runner\Reporter\BasicConsole;
use LaminasTest\Diagnostics\TestAsset\Check\AlwaysSuccess;
use LaminasTest\Diagnostics\TestAsset\Result\Unknown;
use PHPUnit\Framework\TestCase;

class BasicConsoleReporterTest extends TestCase
{
    /**
     * @var BasicConsole
     */
    protected $reporter;

    protected function setUp() : void
    {
        $this->reporter = new BasicConsole();
    }

    public function testStartMessage()
    {
        ob_start();
        $checks = new ArrayObject([new AlwaysSuccess()]);
        $this->reporter->onStart($checks, []);
        $this->assertStringMatchesFormat('Starting%A', ob_get_clean());
    }

    public function testProgressDots()
    {
        $checks = new ArrayObject(array_fill(0, 5, new AlwaysSuccess()));

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        foreach ($checks as $alias => $check) {
            $result = new Success();
            $this->reporter->onAfterRun($check, $result, $alias);
        }

        $this->assertEquals('.....', ob_get_clean());
    }

    public function testWarningSymbols()
    {
        $checks = new ArrayObject(array_fill(0, 5, new AlwaysSuccess()));

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        foreach ($checks as $alias => $check) {
            $result = new Warning();
            $this->reporter->onAfterRun($check, $result, $alias);
        }

        $this->assertEquals('!!!!!', ob_get_clean());
    }

    public function testFailureSymbols()
    {
        $checks = new ArrayObject(array_fill(0, 5, new AlwaysSuccess()));

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        foreach ($checks as $alias => $check) {
            $result = new Failure();
            $this->reporter->onAfterRun($check, $result, $alias);
        }

        $this->assertEquals('FFFFF', ob_get_clean());
    }

    public function testUnknownSymbols()
    {
        $checks = new ArrayObject(array_fill(0, 5, new AlwaysSuccess()));

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        foreach ($checks as $alias => $check) {
            $result = new Unknown();
            $this->reporter->onAfterRun($check, $result, $alias);
        }

        $this->assertEquals('?????', ob_get_clean());
    }

    public function testProgressDotsNoGutter()
    {
        $this->reporter = new BasicConsole(40);
        $checks = new ArrayObject(array_fill(0, 40, new AlwaysSuccess()));

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        foreach ($checks as $alias => $check) {
            $result = new Success();
            $this->reporter->onAfterRun($check, $result, $alias);
        }

        $this->assertEquals(str_repeat('.', 40), ob_get_clean());
    }

    public function testProgressOverflow()
    {
        $this->reporter = new BasicConsole(40);
        $checks = new ArrayObject(array_fill(0, 80, new AlwaysSuccess()));

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        foreach ($checks as $alias => $check) {
            $result = new Success();
            $this->reporter->onAfterRun($check, $result, $alias);
        }

        $expected  = '......................... 25 / 80 ( 31%)';
        $expected .= '......................... 50 / 80 ( 63%)';
        $expected .= '......................... 75 / 80 ( 94%)';
        $expected .= '.....';

        $this->assertEquals($expected, ob_get_clean());
    }

    public function testProgressOverflowMatch()
    {
        $this->reporter = new BasicConsole(40);
        $checks = new ArrayObject(array_fill(0, 75, new AlwaysSuccess()));

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        foreach ($checks as $alias => $check) {
            $result = new Success();
            $this->reporter->onAfterRun($check, $result, $alias);
        }

        $expected  = '......................... 25 / 75 ( 33%)';
        $expected .= '......................... 50 / 75 ( 67%)';
        $expected .= '......................... 75 / 75 (100%)';

        $this->assertEquals($expected, ob_get_clean());
    }

    public function testSummaryAllSuccessful()
    {
        $checks = new ArrayObject();
        $check = null;
        $results = new Collection();
        for ($x = 0; $x < 20; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Success();
        }

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        $this->reporter->onFinish($results);
        $this->assertStringStartsWith('OK (20 diagnostic tests)', trim(ob_get_clean()));
    }

    public function testSummaryWithWarnings()
    {
        $checks = new ArrayObject();
        $check = null;
        $results = new Collection();
        for ($x = 0; $x < 15; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Success();
        }

        for ($x = 0; $x < 5; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Warning();
        }

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        $this->reporter->onFinish($results);
        $this->assertStringStartsWith('5 warnings, 15 successful tests', trim(ob_get_clean()));
    }

    public function testSummaryWithFailures()
    {
        $checks = new ArrayObject();
        $check = null;
        $results = new Collection();
        for ($x = 0; $x < 15; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Success();
        }

        for ($x = 0; $x < 5; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Warning();
        }

        for ($x = 0; $x < 5; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Failure();
        }

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        $this->reporter->onFinish($results);
        $this->assertStringStartsWith('5 failures, 5 warnings, 15 successful tests', trim(ob_get_clean()));
    }

    public function testSummaryWithUnknowns()
    {
        $checks = new ArrayObject();
        $check = null;
        $results = new Collection();
        for ($x = 0; $x < 15; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Success();
        }

        for ($x = 0; $x < 5; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Warning();
        }

        for ($x = 0; $x < 5; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Failure();
        }

        for ($x = 0; $x < 5; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Unknown();
        }

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        $this->reporter->onFinish($results);
        $this->assertStringMatchesFormat('%A5 unknown test results%A', trim(ob_get_clean()));
    }

    public function testWarnings()
    {
        $checks = new ArrayObject();
        $check = null;
        $results = new Collection();
        for ($x = 0; $x < 15; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Success();
        }

        $checks[] = $check = new AlwaysSuccess();
        $results[$check] = new Warning('foo');

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        $this->reporter->onFinish($results);
        $this->assertStringMatchesFormat(
            '%AWarning: Always Success%wfoo',
            trim(ob_get_clean())
        );
    }

    public function testFailures()
    {
        $checks = new ArrayObject();
        $check = null;
        $results = new Collection();
        for ($x = 0; $x < 15; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Success();
        }

        $checks[] = $check = new AlwaysSuccess();
        $results[$check] = new Failure('bar');

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        $this->reporter->onFinish($results);
        $this->assertStringMatchesFormat(
            '%AFailure: Always Success%wbar',
            trim(ob_get_clean())
        );
    }

    public function testUnknowns()
    {
        $checks = new ArrayObject();
        $check = null;
        $results = new Collection();
        for ($x = 0; $x < 15; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Success();
        }

        $checks[] = $check = new AlwaysSuccess();
        $results[$check] = new Unknown('baz');

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        $this->reporter->onFinish($results);
        $this->assertStringMatchesFormat(
            '%AUnknown result LaminasTest\Diagnostics\TestAsset\Result\Unknown: Always Success%wbaz%A',
            trim(ob_get_clean())
        );
    }

    public function testStoppedNotice()
    {
        $checks = new ArrayObject();
        $check = null;
        $results = new Collection();
        for ($x = 0; $x < 15; $x++) {
            $checks[] = $check = new AlwaysSuccess();
            $results[$check] = new Success();
        }

        ob_start();
        $this->reporter->onStart($checks, []);
        ob_clean();

        $this->reporter->onStop($results);

        $this->reporter->onFinish($results);
        $this->assertStringMatchesFormat('%ADiagnostics aborted%A', trim(ob_get_clean()));
    }

    public function testOnBeforeRun()
    {
        // currently unused
        $this->reporter->onBeforeRun(new AlwaysSuccess(), null);
    }
}
