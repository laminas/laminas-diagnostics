<?php

namespace LaminasTest\Diagnostics;

use InvalidArgumentException;
use Laminas\Diagnostics\Check\ExtensionLoaded;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

use function phpversion;

/** @covers \Laminas\Diagnostics\Check\ExtensionLoaded */
final class ExtensionLoadedTest extends TestCase
{
    /**
     * @dataProvider invalidArgumentProvider
     * @param mixed $extensionName
     */
    public function testInvalidArguments($extensionName): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExtensionLoaded($extensionName);
    }

    public function testCheck(): void
    {
        $check  = new ExtensionLoaded('json');
        $result = $check->check();

        self::assertInstanceof(SuccessInterface::class, $result);
        self::assertSame('json ' . phpversion('json'), $result->getData());

        $check  = new ExtensionLoaded(['json', 'xml']);
        $result = $check->check();

        self::assertInstanceof(SuccessInterface::class, $result);
        self::assertSame(['json' => phpversion('json'), 'xml' => phpversion('xml')], $result->getData());
    }

    public function testCheckMissingExtension(): void
    {
        $check  = new ExtensionLoaded('unknown-foo');
        $result = $check->check();

        self::assertInstanceof(FailureInterface::class, $result);
        self::assertSame(['unknown-foo'], $result->getData());

        $check  = new ExtensionLoaded(['unknown-foo', 'unknown-bar']);
        $result = $check->check();

        self::assertInstanceof(FailureInterface::class, $result);
        self::assertSame(['unknown-foo', 'unknown-bar'], $result->getData());
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function invalidArgumentProvider(): array
    {
        return [
            [new stdClass()],
            [100],
        ];
    }
}
