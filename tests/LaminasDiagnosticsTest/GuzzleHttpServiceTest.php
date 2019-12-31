<?php

namespace LaminasTest\Diagnostics;

use Guzzle\Http\Client as Guzzle3Client;
use Guzzle\Http\Message\Response as Guzzle3Response;
use Guzzle\Plugin\Mock\MockPlugin;
use GuzzleHttp\Client as Guzzle4And5Client;
use GuzzleHttp\Message\Response as Guzzle4And5Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Laminas\Diagnostics\Check\CouchDBCheck;
use Laminas\Diagnostics\Check\GuzzleHttpService;

class GuzzleHttpServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $params
     *
     * @dataProvider couchDbProvider
     */
    public function testCouchDbCheck(array $params)
    {
        $check = new CouchDBCheck($params);
        $this->assertInstanceOf('Laminas\Diagnostics\Check\CouchDbCheck', $check);
    }

    /**
     * @dataProvider checkProvider
     */
    public function testGuzzle3Check($content, $actualContent, $actualStatusCode, $resultClass, $method = 'GET', $body = null)
    {
        $check = new GuzzleHttpService(
            'http://www.example.com/foobar',
            array(),
            array(),
            '200',
            $content,
            $this->getMockGuzzle3Client($actualStatusCode, $actualContent),
            $method,
            $body
        );
        $result = $check->check();

        $this->assertInstanceOf($resultClass, $result);
    }

    /**
     * @dataProvider checkProvider
     */
    public function testGuzzle4And5Check($content, $actualContent, $actualStatusCode, $resultClass, $method = 'GET', $body = null)
    {
        if (!class_exists('GuzzleHttp\Client')) {
            $this->markTestSkipped('guzzlehttp/guzzle not installed.');
        }

        $check = new GuzzleHttpService(
            'http://www.example.com/foobar',
            array(),
            array(),
            '200',
            $content,
            $this->getMockGuzzle4And5Client($actualStatusCode, $actualContent),
            $method,
            $body
        );
        $result = $check->check();

        $this->assertInstanceOf($resultClass, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidClient()
    {
        $check = new GuzzleHttpService('http://example.com', array(), array(), 200, null, 'not guzzle');
    }

    public function checkProvider()
    {
        return array(
            array(null, null, 200, 'Laminas\Diagnostics\Result\SuccessInterface'),
            array(null, null, 200, 'Laminas\Diagnostics\Result\SuccessInterface', 'POST', array('key' => 'value')),
            array(null, null, 200, 'Laminas\Diagnostics\Result\SuccessInterface', 'PUT'),
            array(null, null, 404, 'Laminas\Diagnostics\Result\FailureInterface'),
            array(null, null, 404, 'Laminas\Diagnostics\Result\FailureInterface', 'POST', array('key' => 'value')),
            array(null, null, 404, 'Laminas\Diagnostics\Result\FailureInterface', 'PUT'),
            array('foo', 'foobar', 200, 'Laminas\Diagnostics\Result\SuccessInterface'),
            array('foo', 'foobar', 200, 'Laminas\Diagnostics\Result\SuccessInterface', 'POST', array('key' => 'value')),
            array('foo', 'foobar', 200, 'Laminas\Diagnostics\Result\SuccessInterface', 'PUT'),
            array('baz', 'foobar', 200, 'Laminas\Diagnostics\Result\FailureInterface'),
            array('baz', 'foobar', 200, 'Laminas\Diagnostics\Result\FailureInterface', 'POST', array('key' => 'value')),
            array('baz', 'foobar', 200, 'Laminas\Diagnostics\Result\FailureInterface', 'PUT'),
            array('baz', 'foobar', 500, 'Laminas\Diagnostics\Result\FailureInterface'),
        );
    }

    public function couchDbProvider()
    {
        return array(
            array(array('url' => 'http://root:party@localhost/hello')),
            array(array('host' => '127.0.0.1', 'port' => '443', 'username' => 'test', 'password' => 'test', 'dbname' => 'database')),
        );
    }

    private function getMockGuzzle3Client($statusCode = 200, $content = null)
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Guzzle3Response($statusCode, null, $content));

        $client = new Guzzle3Client();
        $client->addSubscriber($plugin);

        return $client;
    }

    private function getMockGuzzle4And5Client($statusCode = 200, $content = null)
    {
        $client = new Guzzle4And5Client();
        $client->getEmitter()->attach(new Mock(array(new Guzzle4And5Response($statusCode, array(), Stream::factory((string) $content)))));

        return $client;
    }
}
