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
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface;

use function array_merge;
use function class_exists;
use function http_build_query;
use function is_array;
use function is_object;
use function is_string;
use function json_encode;
use function method_exists;
use function sprintf;
use function str_contains;
use function strstr;

class GuzzleHttpService extends AbstractCheck
{
    /** @var array */
    protected $options;

    /** @var PsrRequestInterface */
    protected $request;

    /** @var int */
    protected $statusCode;

    /** @var GuzzleClient */
    protected $guzzle;

    /**
     * @param string|PsrRequestInterface $requestOrUrl
     *     The absolute url to check, or a fully-formed request instance.
     * @param array $headers An array of headers used to create the request
     * @param array $options An array of guzzle options to use when sending the request
     * @param int $statusCode The response status code to check
     * @param null|string $content The response content to check
     * @param null|GuzzleClientInterface $guzzle Instance of guzzle to use
     * @param string $method The method of the request
     * @param string|iterable|object $body The body of the request (used for POST, PUT and DELETE requests)
     * @throws InvalidArgumentException
     */
    public function __construct(
        $requestOrUrl,
        array $headers = [],
        array $options = [],
        $statusCode = 200,
        protected ?string $content = null,
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

        $this->options    = $options;
        $this->statusCode = (int) $statusCode;
    }

    /**
     * @see Laminas\Diagnostics\CheckInterface::check()
     *
     * @return ResultInterface
     */
    public function check()
    {
        return $this->performGuzzleRequest();
    }

    /**
     * @param string $url
     * @param string $method
     * @return PsrRequestInterface
     */
    private function createRequestFromConstructorArguments($url, $method, array $headers, mixed $body, array $options)
    {
        return $this->createPsr7Request($url, $method, $headers, $body);
    }

    /**
     * @param string $url
     * @param string $method
     * @return PsrRequestInterface
     * @throws InvalidArgumentException If unable to determine how to serialize the body content.
     */
    private function createPsr7Request($url, $method, array $headers, mixed $body)
    {
        $request = new PsrRequest($method, $url, $headers);
        if (empty($body)) {
            return $request;
        }

        // These can all be handled directly by the stream factory
        if (
            is_string($body)
            || $body instanceof Iterator
            || (is_object($body) && method_exists($body, '__toString'))
        ) {
            return $request->withBody(Utils::streamFor($body));
        }

        // If we have an array or JSON serializable object of data, and we've
        // indicated JSON payload content, we can serialize it and create a
        // stream.
        if (
            strstr($request->getHeaderLine('Content-Type'), 'json')
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
     * @return GuzzleClient
     * @throws Exception
     */
    private function createGuzzleClient()
    {
        if (! class_exists(GuzzleClient::class)) {
            throw new Exception('Guzzle is required.');
        }

        return new GuzzleClient();
    }

    /**
     * @return ResultInterface
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
     * @param ResponseInterface $response
     * @return ResultInterface
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
        return ! $this->content || str_contains($content, $this->content)
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
