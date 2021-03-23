<?php

namespace LaminasTest\Diagnostics;

use ArrayObject;
use Enlightn\SecurityChecker\AdvisoryAnalyzer;
use ErrorException;
use Exception;
use InvalidArgumentException;
use Laminas\Diagnostics\Check\Callback;
use Laminas\Diagnostics\Check\ClassExists;
use Laminas\Diagnostics\Check\CpuPerformance;
use Laminas\Diagnostics\Check\DirReadable;
use Laminas\Diagnostics\Check\DirWritable;
use Laminas\Diagnostics\Check\ExtensionLoaded;
use Laminas\Diagnostics\Check\IniFile;
use Laminas\Diagnostics\Check\JsonFile;
use Laminas\Diagnostics\Check\Memcache;
use Laminas\Diagnostics\Check\Memcached;
use Laminas\Diagnostics\Check\Mongo;
use Laminas\Diagnostics\Check\PhpFlag;
use Laminas\Diagnostics\Check\PhpVersion;
use Laminas\Diagnostics\Check\ProcessRunning;
use Laminas\Diagnostics\Check\RabbitMQ;
use Laminas\Diagnostics\Check\Redis;
use Laminas\Diagnostics\Check\StreamWrapperExists;
use Laminas\Diagnostics\Check\XmlFile;
use Laminas\Diagnostics\Check\YamlFile;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\Warning;
use LaminasTest\Diagnostics\TestAsset\Check\AlwaysSuccess;
use LaminasTest\Diagnostics\TestAsset\Check\SecurityAdvisory;
use PHPUnit\Framework\TestCase;
use stdClass;

class ChecksTest extends TestCase
{
    public function testLabels(): void
    {
        $label = md5(rand());
        $check = new AlwaysSuccess();
        $check->setLabel($label);
        self::assertEquals($label, $check->getLabel());
    }

    public function testCpuPerformance(): void
    {
        $check = new CpuPerformance(0); // minimum threshold
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new CpuPerformance(999999999); // improbable to archive
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
    }

    public function testRabbitMQ(): void
    {
        if (getenv('TESTS_LAMINAS_DIAGNOSTICS_RABBITMQ_ENABLED') !== 'true') {
            self::markTestSkipped('RabbitMQ tests are not enabled; enable them in phpunit.xml');
        }

        $check = new RabbitMQ();
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new RabbitMQ('127.0.0.250', 9999);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
    }

    public function testRedis(): void
    {
        if (getenv('TESTS_LAMINAS_DIAGNOSTICS_REDIS_ENABLED') !== 'true') {
            self::markTestSkipped('Redis tests are not enabled; enable them in phpunit.xml');
        }

        $check = new Redis();
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new Redis('127.0.0.250', 9999);
        $this->expectException(Exception::class);
        $check->check();
    }

    public function testMemcache(): void
    {
        if (getenv('TESTS_LAMINAS_DIAGNOSTICS_MEMCACHE_ENABLED') !== 'true') {
            self::markTestSkipped('Memcache tests are not enabled; enable them in phpunit.xml');
        }

        $check = new Memcache();
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new Memcache('127.0.0.250', 9999);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
    }

    public function testMemcached(): void
    {
        if (getenv('TESTS_LAMINAS_DIAGNOSTICS_MEMCACHED_ENABLED') !== 'true') {
            self::markTestSkipped('Memcached tests are not enabled; enable them in phpunit.xml');
        }

        $check = new Memcached();
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new Memcached('127.0.0.250', 9999);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
    }

    public function testMongo(): void
    {
        if (getenv('TESTS_LAMINAS_DIAGNOSTICS_MONGO_ENABLED') !== 'true') {
            self::markTestSkipped('Mongo tests are not enabled; enable them in phpunit.xml');
        }

        $check = new Mongo();
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new Memcached('127.0.0.250', 9999);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
    }

    public function testClassExists(): void
    {
        $check = new ClassExists(__CLASS__);
        self::assertInstanceOf(Success::class, $check->check());

        $check = new ClassExists('improbableClassNameInGlobalNamespace999999999999999999');
        self::assertInstanceOf(Failure::class, $check->check());

        $check = new ClassExists([
            __CLASS__,
            Success::class,
            Failure::class,
            Warning::class,
        ]);
        self::assertInstanceOf(Success::class, $check->check());

        $check = new ClassExists([
            __CLASS__,
            Success::class,
            'improbableClassNameInGlobalNamespace999999999999999999',
            Failure::class,
            Warning::class,
        ]);
        self::assertInstanceOf(Failure::class, $check->check());
    }

    public function testClassExistsExplanation(): void
    {
        $check = new ClassExists([
            __CLASS__,
            Success::class,
            'improbableClassNameInGlobalNamespace888',
            'improbableClassNameInGlobalNamespace999',
            Failure::class,
            Warning::class,
        ]);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%simprobableClassNameInGlobalNamespace888%s', $result->getMessage());
        self::assertStringMatchesFormat('%simprobableClassNameInGlobalNamespace999', $result->getMessage());
    }

    public function testPhpVersion(): void
    {
        $check = new PhpVersion(PHP_VERSION); // default operator
        self::assertInstanceOf(Success::class, $check->check());

        $check = new PhpVersion(PHP_VERSION, '='); // explicit equal
        self::assertInstanceOf(Success::class, $check->check());

        $check = new PhpVersion(PHP_VERSION, '<'); // explicit less than
        self::assertInstanceOf(Failure::class, $check->check());
    }

    public function testPhpVersionArray(): void
    {
        $check = new PhpVersion([PHP_VERSION]); // default operator
        self::assertInstanceOf(Success::class, $check->check());

        $check = new PhpVersion([
            '1.0.0',
            '1.1.0',
            '1.1.1',
        ], '<'); // explicit less than
        self::assertInstanceOf(Failure::class, $check->check());

        $check = new PhpVersion(new ArrayObject([
            '40.0.0',
            '41.0.0',
            '42.0.0',
        ]), '<'); // explicit less than
        self::assertInstanceOf(Success::class, $check->check());

        $check = new PhpVersion(new ArrayObject([
            '41.0.0',
            PHP_VERSION,
        ]), '!='); // explicit less than
        self::assertInstanceOf(Failure::class, $check->check());
    }

    public function testCallback(): void
    {
        $called = false;
        $expectedResult = new Success();
        $check = new Callback(function () use (&$called, $expectedResult) {
            $called = true;

            return $expectedResult;
        });
        $result = $check->check();
        self::assertTrue($called);
        self::assertSame($expectedResult, $result);
    }

    public function testExtensionLoaded(): void
    {
        $allExtensions = get_loaded_extensions();
        $ext1 = $allExtensions[array_rand($allExtensions)];

        $check = new ExtensionLoaded($ext1);
        self::assertInstanceOf(Success::class, $check->check());

        $check = new ExtensionLoaded('improbableExtName999999999999999999');
        self::assertInstanceOf(Failure::class, $check->check());

        $extensions = [];
        foreach (array_rand($allExtensions, 3) as $key) {
            $extensions[] = $allExtensions[$key];
        }

        $check = new ExtensionLoaded($extensions);
        self::assertInstanceOf(Success::class, $check->check());

        $extensions[] = 'improbableExtName9999999999999999999999';

        $check = new ExtensionLoaded($extensions);
        self::assertInstanceOf(Failure::class, $check->check());

        $extensions = [
            'improbableExtName9999999999999999999999',
            'improbableExtName0000000000000000000000',
        ];

        $check = new ExtensionLoaded($extensions);
        self::assertInstanceOf(Failure::class, $check->check());
    }

    public function testPhpFlag(): void
    {
        // Retrieve a set of settings to test against
        $all = ini_get_all();

        foreach ($all as $name => $valueArray) {
            if ($valueArray['local_value'] == '0') {
                break;
            }
        }
        $check = new PhpFlag($name, false);
        self::assertInstanceOf(Success::class, $check->check());

        $check = new PhpFlag($name, true);
        self::assertInstanceOf(Failure::class, $check->check());


        $allFalse = [];
        foreach ($all as $name => $valueArray) {
            if ($valueArray['local_value'] == '0') {
                $allFalse[] = $name;
            }

            if (count($allFalse) == 3) {
                break;
            }
        }

        $check = new PhpFlag($allFalse, false);
        self::assertInstanceOf(Success::class, $check->check());

        $check = new PhpFlag($allFalse, true);
        self::assertInstanceOf(Failure::class, $result = $check->check());
        self::assertStringMatchesFormat('%A' . join(', ', $allFalse) . '%Aenabled%A', $result->getMessage());

        $allFalse = new ArrayObject($allFalse);
        $check = new PhpFlag($allFalse, false);
        self::assertInstanceOf(Success::class, $check->check());

        $check = new PhpFlag($allFalse, true);
        self::assertInstanceOf(Failure::class, $check->check());

        $notAllFalse = $allFalse;
        foreach ($all as $name => $valueArray) {
            if ($valueArray['local_value'] == '1') {
                $notAllFalse[] = $name;
                break;
            }
        }

        $check = new PhpFlag($notAllFalse, false);
        self::assertInstanceOf(Failure::class, $result = $check->check());
        self::assertStringMatchesFormat("%A$name%A", $result->getMessage());

        $check = new PhpFlag($notAllFalse, true);
        self::assertInstanceOf(Failure::class, $check->check());
    }

    public function testStreamWrapperExists(): void
    {
        $allWrappers = stream_get_wrappers();
        $wrapper = $allWrappers[array_rand($allWrappers)];

        $check = new StreamWrapperExists($wrapper);
        self::assertInstanceOf(Success::class, $check->check());

        $check = new StreamWrapperExists('improbableName999999999999999999');
        self::assertInstanceOf(Failure::class, $check->check());

        $wrappers = [];
        foreach (array_rand($allWrappers, 3) as $key) {
            $wrappers[] = $allWrappers[$key];
        }

        $check = new StreamWrapperExists($wrappers);
        self::assertInstanceOf(Success::class, $check->check());

        $wrappers[] = 'improbableName9999999999999999999999';

        $check = new StreamWrapperExists($wrappers);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%simprobableName9999999999999999999999%s', $result->getMessage());

        $wrappers = [
            'improbableName9999999999999999999999',
            'improbableName0000000000000000000000',
        ];

        $check = new StreamWrapperExists($wrappers);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%simprobableName9999999999999999999999%s', $result->getMessage());
        self::assertStringMatchesFormat('%simprobableName0000000000000000000000', $result->getMessage());
    }

    public function testDirReadable(): void
    {
        $check = new DirReadable(__DIR__);
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new DirReadable([
            __DIR__,
            __DIR__ . '/../'
        ]);
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result, 'Array of valid dirs');

        $check = new DirReadable(__FILE__);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result, 'An existing file');

        $check = new DirReadable(__DIR__ . '/improbabledir99999999999999999999999999999999999999');
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result, 'Single non-existent dir');

        $check = new DirReadable(__DIR__ . '/improbabledir999999999999');
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%simprobabledir999999999999%s', $result->getMessage());

        $check = new DirReadable([
            __DIR__ . '/improbabledir888888888888',
            __DIR__ . '/improbabledir999999999999',
        ]);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%simprobabledir888888888888%s', $result->getMessage());
        self::assertStringMatchesFormat('%simprobabledir999999999999', $result->getMessage());

        // create a barrage of unreadable directories
        $tmpDir = sys_get_temp_dir();
        if (! is_dir($tmpDir) || ! is_writable($tmpDir)) {
            self::markTestSkipped('Cannot access writable system temp dir to perform the test... ');

            return;
        }

        // generate a random dir name
        while (($dir1 = $tmpDir . '/test' . mt_rand(1, PHP_INT_MAX)) && file_exists($dir1)) {
        }
        while (($dir2 = $tmpDir . '/test' . mt_rand(1, PHP_INT_MAX)) && file_exists($dir2)) {
        }

        // create temporary unreadable directories
        if (! mkdir($dir1) || ! chmod($dir1, 0000) ||
            ! mkdir($dir2) || ! chmod($dir2, 0000)
        ) {
            self::markTestSkipped('Cannot create unreadable temporary directory to perform the test... ');

            return;
        }

        // run the check
        $check = new DirReadable([
            $dir1, // unreadable
            $dir2, // unreadable
            $tmpDir, // valid one
            __DIR__ . '/simprobabledir999999999999', // non existent
        ]);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%s' . $dir1 . '%s', $result->getMessage());
        self::assertStringMatchesFormat('%s' . $dir2 . '%s', $result->getMessage());
        self::assertStringMatchesFormat('%simprobabledir999999999999', $result->getMessage());

        chmod($dir1, 0777);
        chmod($dir2, 0777);
        rmdir($dir1);
        rmdir($dir2);
    }

    public function testDirWritable(): void
    {
        // single non-existent dir
        $path = __DIR__ . '/simprobabledir999999999999';
        $check = new DirWritable($path);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result, 'Non-existent path');
        self::assertStringMatchesFormat($path . '%s', $result->getMessage());

        // non-dir path
        $path = __FILE__;
        $check = new DirWritable($path);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result, 'Non-dir path');
        self::assertStringMatchesFormat($path . '%s', $result->getMessage());

        // multiple non-dir paths
        $path1 = __FILE__;
        $path2 = __DIR__ . '/BasicClassesTest.php';
        $check = new DirWritable([$path1, $path2]);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result, 'Non-dir path');
        self::assertStringMatchesFormat('%s' . $path1 . '%s', $result->getMessage());
        self::assertStringMatchesFormat('%s' . $path2 . '%s', $result->getMessage());

        // create a barrage of unwritable directories
        $tmpDir = sys_get_temp_dir();
        if (! is_dir($tmpDir) || ! is_writable($tmpDir)) {
            self::markTestSkipped('Cannot access writable system temp dir to perform the test... ');

            return;
        }

        // this should succeed
        $check = new DirWritable($tmpDir);
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result, 'Single writable dir');

        // generate a random dir name
        while (($dir1 = $tmpDir . '/test' . mt_rand(1, PHP_INT_MAX)) && file_exists($dir1)) {
        }
        while (($dir2 = $tmpDir . '/test' . mt_rand(1, PHP_INT_MAX)) && file_exists($dir2)) {
        }

        // create temporary writable directories
        if (! mkdir($dir1) || ! mkdir($dir2)) {
            self::markTestSkipped('Cannot create unreadable temporary directory to perform the test... ');

            return;
        }

        // we should now have 3 writable directories
        $check = new DirWritable([
            $tmpDir,
            $dir1,
            $dir2,
        ]);
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result, 'Multiple writable dirs');

        // make temporary dirs unwritable
        if (! chmod($dir1, 0000) || ! chmod($dir2, 0000)) {
            self::markTestSkipped('Cannot create unreadable temporary directory to perform the test... ');

            return;
        }

        // single unwritable dir
        $check = new DirWritable($dir1);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat($dir1 . '%s', $result->getMessage());

        // this should now fail
        $check = new DirWritable([
            $dir1, // unwritable
            $dir2, // unwritable
            $tmpDir, // valid one
            __DIR__ . '/simprobabledir999999999999', // non existent
        ]);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%s' . $dir1 . '%s', $result->getMessage());
        self::assertStringMatchesFormat('%s' . $dir2 . '%s', $result->getMessage());
        self::assertStringMatchesFormat('%simprobabledir999999999999%s', $result->getMessage());

        chmod($dir1, 0777);
        chmod($dir2, 0777);
        rmdir($dir1);
        rmdir($dir2);
    }

    public function testProcessRunning(): void
    {
        if (! $phpPid = @getmypid()) {
            self::markTestSkipped('Unable to retrieve PHP process\' PID');
        }

        $check = new ProcessRunning($phpPid);
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new ProcessRunning(32768);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%sPID 32768%s', $result->getMessage());

        // try to retrieve full PHP process command string
        $phpCommand = shell_exec('ps -o command= -p ' . $phpPid);
        if (! $phpCommand || strlen($phpCommand) < 4) {
            self::markTestSkipped('Unable to retrieve PHP process command name.');
        }

        $check = new ProcessRunning(substr($phpCommand, 0, ceil(strlen($phpPid) / 2)));
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);

        $check = new ProcessRunning('improbable process name 9999999999999999');
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertStringMatchesFormat('%simprobable process name 9999999999999999%s', $result->getMessage());
    }

    public function testSecurityAdvisory(): void
    {
        if (! class_exists(AdvisoryAnalyzer::class)) {
            self::markTestSkipped(
                'Unable to find Enlightn\SecurityChecker\AdvisoryAnalyzer class - probably missing ' .
                'enlightn/security-checker package. Have you installed all dependencies, ' .
                'including those specified require-dev in composer.json?'
            );
        }

        $secureComposerLock = __DIR__ . '/TestAsset/secure-composer.lock';
        $check = new SecurityAdvisory($secureComposerLock);
        $result = $check->check();
        self::assertNotInstanceOf(Failure::class, $result);

        // check against non-existent lock file
        $check = new SecurityAdvisory(__DIR__ . '/improbable-lock-file-99999999999.lock');
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);

        // check against unreadable lock file
        $tmpDir = sys_get_temp_dir();
        if (! is_dir($tmpDir) || ! is_writable($tmpDir)) {
            self::markTestSkipped('Cannot access writable system temp dir to perform the test... ');
            return;
        }
        $unreadableFile = $tmpDir . '/composer.' . uniqid('', true) . '.lock';
        if (! file_put_contents($unreadableFile, 'foo') || ! chmod($unreadableFile, 0000)) {
            self::markTestSkipped('Cannot create temporary file in system temp dir to perform the test... ');
            return;
        }

        $check = new SecurityAdvisory($unreadableFile);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);

        // cleanup
        chmod($unreadableFile, 0666);
        unlink($unreadableFile);
    }

    /**
     * @depends testSecurityAdvisory
     */
    public function testSecurityAdvisoryFailure(): void
    {
        $secureComposerLock = __DIR__ . '/TestAsset/secure-composer.lock';
        $analyzer = $this->createMock(AdvisoryAnalyzer::class);
        $analyzer->expects(self::once())
            ->method('analyzeDependencies')
            ->willReturn([['a' => 1], ['b' => 2], ['c' => 3]]);

        $check = new SecurityAdvisory($secureComposerLock);
        $check->setAdvisoryAnalyzer($analyzer);
        $result = $check->check();
        self::assertInstanceOf(Failure::class, $result);
        self::assertSame('Found security advisories for 3 composer package(s)', $result->getMessage());
    }

    /**
     * @depends testSecurityAdvisory
     */
    public function testSecurityAdvisoryCheckerException(): void
    {
        $secureComposerLock = __DIR__ . '/TestAsset/secure-composer.lock';
        $analyzer = $this->createMock(AdvisoryAnalyzer::class);
        $analyzer->expects(self::once())
            ->method('analyzeDependencies')
            ->will(self::throwException(new Exception));
        $check = new SecurityAdvisory($secureComposerLock);
        $check->setAdvisoryAnalyzer($analyzer);
        $result = $check->check();
        self::assertInstanceOf(Warning::class, $result);
    }

    /**
     * @depends testSecurityAdvisory
     */
    public function testSecurityAdvisoryCheckerSuccess(): void
    {
        $secureComposerLock = __DIR__ . '/TestAsset/secure-composer.lock';
        $checker = $this->createMock(AdvisoryAnalyzer::class);
        $checker->expects(self::once())
            ->method('analyzeDependencies')
            ->willReturn([]);
        $check = new SecurityAdvisory($secureComposerLock);
        $check->setAdvisoryAnalyzer($checker);
        $result = $check->check();
        self::assertInstanceOf(Success::class, $result);
    }

    public function testPhpVersionInvalidVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PhpVersion(new stdClass());
    }

    public function testPhpVersionInvalidVersion2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PhpVersion(fopen('php://memory', 'r'));
    }

    public function testPhpVersionInvalidOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PhpVersion('1.0.0', []);
    }

    public function testPhpVersionInvalidOperator2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PhpVersion('1.0.0', 'like');
    }

    public function testClassExistsInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ClassExists(new stdClass);
    }

    public function testClassExistsInvalidArgument2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ClassExists(15);
    }

    public function testExtensionLoadedInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ExtensionLoaded(new stdClass);
    }

    public function testExtensionLoadedInvalidArgument2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ExtensionLoaded(15);
    }

    public function testDirReadableInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DirReadable(new stdClass);
    }

    public function testDirReadableInvalidArgument2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DirReadable(15);
    }

    public function testDirWritableInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DirWritable(new stdClass);
    }

    public function testDirWritableInvalidArgument2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DirWritable(15);
    }

    public function testStreamWrapperInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamWrapperExists(new stdClass);
    }

    public function testStreamWrapperInvalidInvalidArgument2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamWrapperExists(15);
    }

    public function testCallbackInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Callback(15);
    }

    public function testCallbackInvalidArgument2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Callback([$this, 'foobarbar']);
    }

    public function testCpuPerformanceInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CpuPerformance(-1);
    }

    public function testProcessRunningInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ProcessRunning(new stdClass());
    }

    public function testProcessRunningInvalidArgument2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ProcessRunning(-100);
    }

    public function testProcessRunningInvalidArgument3(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ProcessRunning('');
    }

    /**
     * @depends testSecurityAdvisory
     */
    public function testSecurityAdvisoryInvalidArgument1(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecurityAdvisory($this->createMock(AdvisoryAnalyzer::class), new stdClass());
    }

    public function testAbstractFileCheckArgument1(): void
    {
        $temp = tmpfile();
        fwrite($temp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<foo>1</foo>");
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        // single string
        $check = new XmlFile($path);
        self::assertInstanceOf(SuccessInterface::class, $check->check());

        // array
        $check = new XmlFile([$path, $path, $path]);
        self::assertInstanceOf(SuccessInterface::class, $check->check());

        // object inplementing \Traversable
        $check = new XmlFile(new ArrayObject([$path, $path, $path]));
        self::assertInstanceOf(SuccessInterface::class, $check->check());

        fclose($temp);
    }

    public function testAbstractFileCheckInvalidArgument1(): void
    {
        // int
        try {
            $check = new XmlFile(2);
            self::fail('InvalidArguementException should be thrown here!');
        } catch (Exception $e) {
            self::assertInstanceOf('InvalidArgumentException', $e);
        }

        // bool
        try {
            $check = new XmlFile(true);
            self::fail('InvalidArguementException should be thrown here!');
        } catch (Exception $e) {
            self::assertInstanceOf('InvalidArgumentException', $e);
        }

        // object not implementing \Traversable
        try {
            $check = new XmlFile(new stdClass());
            self::fail('InvalidArguementException should be thrown here!');
        } catch (Exception $e) {
            self::assertInstanceOf('InvalidArgumentException', $e);
        }
    }

    public function testXmlFileValid(): void
    {
        $temp = tmpfile();
        fwrite($temp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<foo>1</foo>");
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        $check = new XmlFile($path);
        self::assertInstanceOf(SuccessInterface::class, $check->check());

        fclose($temp);
    }

    public function testXmlFileInvalid(): void
    {
        $temp = tmpfile();
        fwrite($temp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<foo>1</bar>");
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        $check = new XmlFile($path);
        self::assertInstanceOf(FailureInterface::class, $check->check());

        fclose($temp);
    }

    public function testXmlFileNotPresent(): void
    {
        $check = new XmlFile('/does/not/exist');
        self::assertInstanceOf(FailureInterface::class, $check->check());
    }

    public function testIniFileValid(): void
    {
        $temp = tmpfile();
        fwrite($temp, "[first_group]\nfoo = 1\nbar = 5");
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        $check = new IniFile($path);
        self::assertInstanceOf(SuccessInterface::class, $check->check());

        fclose($temp);
    }

    public function testIniFileInvalid(): void
    {
        $temp = tmpfile();
        fwrite($temp, "]]]]]]");
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        $check = new IniFile($path);
        self::assertInstanceOf(FailureInterface::class, $check->check());

        fclose($temp);
    }

    public function testIniFileNotPresent(): void
    {
        $check = new IniFile('/does/not/exist');
        self::assertInstanceOf(FailureInterface::class, $check->check());
    }

    public function testYamlFileValid(): void
    {
        $temp = tmpfile();
        fwrite($temp, "foo: 1\nbar: 1");
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        $check = new YamlFile($path);
        self::assertInstanceOf(SuccessInterface::class, $check->check());

        fclose($temp);
    }

    public function testYamlFileInvalid(): void
    {
        $temp = tmpfile();
        fwrite($temp, "foo: 1\n\tbar: 3");
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        $check = new YamlFile($path);
        self::assertInstanceOf(FailureInterface::class, $check->check());

        fclose($temp);
    }

    public function testYamlFileNotPresent(): void
    {
        $check = new YamlFile('/does/not/exist');
        self::assertInstanceOf(FailureInterface::class, $check->check());
    }

    public function testJsonFileValid(): void
    {
        $temp = tmpfile();
        fwrite($temp, '{ "foo": "bar"}');
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        $check = new JsonFile($path);
        self::assertInstanceOf(SuccessInterface::class, $check->check());

        fclose($temp);
    }

    public function testJsonFileInvalid(): void
    {
        $temp = tmpfile();
        fwrite($temp, '{ foo: {"bar"');
        $meta = stream_get_meta_data($temp);
        $path = $meta['uri'];

        $check = new JsonFile($path);
        self::assertInstanceOf(FailureInterface::class, $check->check());

        fclose($temp);
    }

    public function testJsonFileNotPresent(): void
    {
        $check = new JsonFile('/does/not/exist');
        self::assertInstanceOf(FailureInterface::class, $check->check());
    }
}
