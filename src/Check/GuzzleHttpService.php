<?php

namespace Laminas\Diagnostics\Check;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Iterator;
use JsonSerializable;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use RuntimeException;

class GuzzleHttpService extends AbstractCheck
{
    protected $content;
    protected $options;
    protected $request;
    protected $statusCode;
    protected $guzzle;

    /**
     * @param string|PsrRequestInterface $requestOrUrl
     *     The absolute url to check, or a fully-formed request instance.
     * @param array $headers An array of headers used to create the request
     * @param array $options An array of guzzle options to use when sending the request
     * @param int $statusCode The response status code to check
     * @param null $content The response content to check
     * @param null|GuzzleClientInterface $guzzle Instance of guzzle to use
     * @param string $method The method of the request
     * @param mixed $body The body of the request (used for POST, PUT and DELETE requests)
     * @throws InvalidArgumentException
     */
    public function __construct(
        $requestOrUrl,
        array $headers = [],
        array $options = [],
        $statusCode = 200,
        $content = null,
        $guzzle = null,
        $method = 'GET',
        $body = null
    ) {
        if (! $guzzle) {
            $guzzle = $this->createGuzzleClient();
        }

        if (! $guzzle instanceof GuzzleClientInterface) {
            throw new InvalidArgumentException(
                'Parameter "guzzle" must be an instance of GuzzleHttp\ClientInterface'
            );
        }

        $this->guzzle = $guzzle;

        $this->request = $requestOrUrl instanceof PsrRequestInterface
            ? $requestOrUrl
            : $this->createRequestFromConstructorArguments($requestOrUrl, $method, $headers, $body, $options);

        $this->options = $options;
        $this->statusCode = (int) $statusCode;
        $this->content = $content;
    }

    /**
     * @see Laminas\Diagnostics\CheckInterface::check()
     */
    public function check()
    {
        return $this->performGuzzleRequest();
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $headers
     * @param mixed $body
     * @param array $options
     * @return PsrRequestInterface
     */
    private function createRequestFromConstructorArguments($url, $method, array $headers, $body, array $options)
    {
        return $this->createPsr7Request($url, $method, $headers, $body);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $headers
     * @param mixed $body
     * @return PsrRequestInterface
     * @throws InvalidArgumentException if unable to determine how to serialize
     *     the body content.
     */
    private function createPsr7Request($url, $method, array $headers, $body)
    {
        $request = new PsrRequest($method, $url, $headers);
        if (empty($body)) {
            return $request;
        }

        // These can all be handled directly by the stream factory
        if (is_string($body)
            || $body instanceof Iterator
            || (is_object($body) && method_exists($body, '__toString'))
        ) {
            return $request->withBody(Utils::streamFor($body));

        }

        // If we have an array or JSON serializable object of data, and we've
        // indicated JSON payload content, we can serialize it and create a
        // stream.
        if (strstr($request->getHeaderLine('Content-Type'), 'json')
            && (is_array($body) || $body instanceof JsonSerializable)
        ) {
            return $request->withBody(Utils::streamFor(json_encode($body)));
        }

        // If we have an array of data at this point, we'll assume we want
        // form-encoded data.
        if (is_array($body)) {
            return $request->withBody(Utils::streamFor(http_build_query($body, '', '&')));
        }

        throw new InvalidArgumentException(
            'Unable to create Guzzle request; invalid $body provided'
        );
    }

    /**
     * @return \GuzzleHttp\Client
     *
     * @throws \Exception
     */
    private function createGuzzleClient()
    {
        if (! class_exists(GuzzleClient::class)) {
            throw new Exception('Guzzle is required.');
        }

        return new GuzzleClient();
    }

    /**
     * @return \Laminas\Diagnostics\Result\ResultInterface
     */
    private function performGuzzleRequest()
    {
        $response = $this->guzzle->send(
            $this->request,
            array_merge(
                [
                    'exceptions' => false,
                ],
                $this->options
            )
        );
        return $this->analyzeResponse($response);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Laminas\Diagnostics\Result\ResultInterface
     */
    private function analyzeResponse($response)
    {
        $result = $this->analyzeStatusCode((int) $response->getStatusCode());
        if ($result instanceof Failure) {
            return $result;
        }

        $result = $this->analyzeResponseContent((string) $response->getBody());
        if ($result instanceof Failure) {
            return $result;
        }

        return new Success();
    }

    /**
     * @param int $statusCode
     * @return bool|FailureInterface Returns boolean true when successful, and
     *     a FailureInterface instance otherwise
     */
    private function analyzeStatusCode($statusCode)
    {
        return $this->statusCode === $statusCode
            ? true
            : new Failure(sprintf(
                'Status code %s does not match %s in response from %s',
                $this->statusCode,
                $statusCode,
                $this->getUri()
            ));
    }

    /**
     * @param string $content
     * @return bool|FailureInterface Returns boolean true when successful, and
     *     a FailureInterface instance otherwise
     */
    private function analyzeResponseContent($content)
    {
        return ! $this->content || false !== strpos($content, $this->content)
            ? true
            : new Failure(sprintf(
                'Content %s not found in response from %s',
                $this->content,
                $this->getUri()
            ));
    }

    /**
     * @return string
     */
    private function getUri()
    {
        return (string) $this->request->getUri();
    }
}
