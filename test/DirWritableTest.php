<?php

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Check\DirWritable;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/** @covers \Laminas\Diagnostics\Check\DirWritable */
final class DirWritableTest extends TestCase
{
    private string $checkClass = DirWritable::class;

    public function testShouldImplementCheckInterface(): void
    {
        self::assertInstanceOf(CheckInterface::class, $this->createMock($this->checkClass));
    }

    /**
     * @dataProvider providerValidConstructorArguments
     * @param string|array<int, string> $arguments
     */
    public function testConstructor($arguments): void
    {
        new DirWritable($arguments);

        $this->expectNotToPerformAssertions();
    }

    public function providerValidConstructorArguments(): array
    {
        return [
            [__DIR__],
            [vfsStream::setup()->url()],
            [[__DIR__, vfsStream::setup()->url()]],
        ];
    }

    public function testCheckSuccessSinglePath(): void
    {
        $object = new DirWritable(vfsStream::setup()->url());
        $r      = $object->check();

        self::assertInstanceOf(Success::class, $r);
        self::assertSame('The path is a writable directory.', $r->getMessage());
    }

    public function testCheckSuccessMultiplePaths(): void
    {
        $object = new DirWritable([__DIR__, vfsStream::setup()->url()]);
        $r      = $object->check();

        self::assertInstanceOf(Success::class, $r);
        self::assertSame('All paths are writable directories.', $r->getMessage());
    }

    public function testCheckFailureSingleInvalidDir(): void
    {
        $object = new DirWritable('notadir');
        $r      = $object->check();

        self::assertInstanceOf(Failure::class, $r);
        self::assertStringContainsString('notadir is not a valid directory.', $r->getMessage());
    }

    public function testCheckFailureMultipleInvalidDirs(): void
    {
        $object = new DirWritable(['notadir1', 'notadir2']);
        $r      = $object->check();

        self::assertInstanceOf(Failure::class, $r);
        self::assertStringContainsString(
            'The following paths are not valid directories: notadir1, notadir2.',
            $r->getMessage()
        );
    }

    public function testCheckFailureSingleUnwritableDir(): void
    {
        $root          = vfsStream::setup();
        $unwritableDir = vfsStream::newDirectory('unwritabledir', 000)->at($root);
        $object        = new DirWritable($unwritableDir->url());
        $r             = $object->check();

        self::assertInstanceOf(Failure::class, $r);
        self::assertSame('vfs://root/unwritabledir directory is not writable.', $r->getMessage());
    }

    public function testCheckFailureMultipleUnwritableDirs(): void
    {
        $root           = vfsStream::setup();
        $unwritableDir1 = vfsStream::newDirectory('unwritabledir1', 000)->at($root);
        $unwritableDir2 = vfsStream::newDirectory('unwritabledir2', 000)->at($root);

        $object = new DirWritable([$unwritableDir1->url(), $unwritableDir2->url()]);
        $r      = $object->check();

        self::assertInstanceOf(Failure::class, $r);
        self::assertSame(
            'The following directories are not writable: vfs://root/unwritabledir1, vfs://root/unwritabledir2.',
            $r->getMessage()
        );
    }
}
