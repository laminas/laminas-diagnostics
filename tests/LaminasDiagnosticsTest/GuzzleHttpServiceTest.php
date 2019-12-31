<?php

namespace LaminasTest\Diagnostics;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Laminas\Diagnostics\Check\GuzzleHttpService;

class GuzzleHttpServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider checkProvider
     */
    public function testCheck($content, $actualContent, $actualStatusCode, $resultClass)
    {
        $check = new GuzzleHttpService(
            'http://www.example.com/foobar',
            array(),
            array(),
            200,
            $content,
            $this->getMockClient($actualStatusCode, $actualContent)
        );
        $result = $check->check();

        $this->assertInstanceOf($resultClass, $result);
    }

    public function checkProvider()
    {
        return array(
            array(null, null, 200, 'Laminas\Diagnostics\Result\SuccessInterface'),
            array(null, null, 404, 'Laminas\Diagnostics\Result\FailureInterface'),
            array('foo', 'foobar', 200, 'Laminas\Diagnostics\Result\SuccessInterface'),
            array('baz', 'foobar', 200, 'Laminas\Diagnostics\Result\FailureInterface')
        );
    }

    private function getMockClient($statusCode = 200, $content = null)
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response($statusCode, null, $content));

        $client = new Client(null, array(
            'request.options' => array(
                'exceptions' => false
            )
        ));
        $client->addSubscriber($plugin);

        return $client;
    }
}
