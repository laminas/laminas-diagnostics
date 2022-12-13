<?php

declare(strict_types=1);

namespace LaminasTest\Diagnostics;

use Laminas\Diagnostics\Check\ExtensionLoaded;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use PHPUnit\Framework\TestCase;

use function phpversion;

/** @covers \Laminas\Diagnostics\Check\ExtensionLoaded */
final class ExtensionLoadedTest extends TestCase
{
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
}
