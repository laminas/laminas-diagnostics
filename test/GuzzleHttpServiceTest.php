<?php

namespace LaminasTest\Diagnostics;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler as Guzzle6MockHandler;
use GuzzleHttp\Psr7\Message;
use InvalidArgumentException;
use Laminas\Diagnostics\Check\CouchDBCheck;
use Laminas\Diagnostics\Check\GuzzleHttpService;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ReflectionClass;
use ReflectionProperty;

class GuzzleHttpServiceTest extends TestCase
{
    protected $responseTemplate = <<< 'EOR'
HTTP/1.1 %d

%s
EOR;

    /**
     * @param array $params
     *
     * @dataProvider couchDbProvider
     */
    public function testCouchDbCheck(array $params): void
    {
        $check = new CouchDBCheck($params);
        self::assertInstanceOf(CouchDBCheck::class, $check);
    }

    /**
     * @dataProvider checkProvider
     */
    public function testGuzzleCheck(
        $content,
        $actualContent,
        $actualStatusCode,
        $resultClass,
        $method = 'GET',
        $body = null
    ): void {
        if (! class_exists(GuzzleClient::class)) {
            self::markTestSkipped('guzzlehttp/guzzle not installed.');
        }

        $check = new GuzzleHttpService(
            'http://www.example.com/foobar',
            [],
            [],
            '200',
            $content,
            $this->getMockGuzzleClient($actualStatusCode, $actualContent),
            $method,
            $body
        );
        $result = $check->check();

        self::assertInstanceOf($resultClass, $result);
    }

    public function testInvalidClient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GuzzleHttpService('http://example.com', [], [], 200, null, 'not guzzle');
    }

    public function testCanSendJsonRequests(): void
    {
        $diagnostic = new GuzzleHttpService(
            'https://example.com/foobar',
            ['Content-Type' => 'application/json'],
            [],
            200,
            null,
            null,
            'POST',
            ['foo' => 'bar']
        );

        $r = new ReflectionProperty($diagnostic, 'request');
        $r->setAccessible(true);
        $request = $r->getValue($diagnostic);

        if ($request instanceof RequestInterface) {
            self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        } else {
            self::assertSame('application/json', $request->getHeader('Content-Type'));
        }

        $body = (string) $request->getBody();
        self::assertSame(['foo' => 'bar'], json_decode($body, true));
    }

    public function testCanSendArbitraryRequests(): void
    {
        self::markTestSkipped('Clarify what to do with assertion of protected property.');

        $request = $this->prophesize(RequestInterface::class)->reveal();

        $diagnostic = new GuzzleHttpService($request);

        $this->assertAttributeSame($request, 'request', $diagnostic);
    }

    public function checkProvider(): array
    {
        return [
            [null, null, 200, SuccessInterface::class],
            [null, null, 200, SuccessInterface::class, 'POST', ['key' => 'value']],
            [null, null, 200, SuccessInterface::class, 'PUT'],
            [null, null, 404, FailureInterface::class],
            [null, null, 404, FailureInterface::class, 'POST', ['key' => 'value']],
            [null, null, 404, FailureInterface::class, 'PUT'],
            ['foo', 'foobar', 200, SuccessInterface::class],
            ['foo', 'foobar', 200, SuccessInterface::class, 'POST', ['key' => 'value']],
            ['foo', 'foobar', 200, SuccessInterface::class, 'PUT'],
            ['baz', 'foobar', 200, FailureInterface::class],
            ['baz', 'foobar', 200, FailureInterface::class, 'POST', ['key' => 'value']],
            ['baz', 'foobar', 200, FailureInterface::class, 'PUT'],
            ['baz', 'foobar', 500, FailureInterface::class],
        ];
    }

    public function couchDbProvider(): array
    {
        return [
            'url' => [[
                'url' => 'http://root:party@localhost/hello'
            ]],
            'options' => [[
                'host' => '127.0.0.1',
                'port' => '443',
                'username' => 'test',
                'password' => 'test',
                'dbname' => 'database'
            ]],
        ];
    }

    private function getMockGuzzleClient($statusCode = 200, $content = null)
    {
        $response = Message::parseResponse(sprintf($this->responseTemplate, $statusCode, (string) $content));

        $handler = new Guzzle6MockHandler();
        $handler->append($response);

        return new GuzzleClient(['handler' => $handler]);
    }
}
