<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

class CurlHttpService extends AbstractCheck
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string|null
     */
    protected $content;

    /**
     * @param string $url The URL to check
     * @param array $options An array of cURL options to use when sending the request
     * @param int $statusCode The response status code to check
     * @param string|null $content The response content to check
     */
    public function __construct(
        $url,
        array $options = [],
        $statusCode = 200,
        $content = null
    ) {
        $this->url = $url;
        $this->options = $options;
        $this->statusCode = (int) $statusCode;
        $this->content = $content;
    }

    public function check()
    {
        // Check if curl extension is present
        // @codeCoverageIgnoreStart
        if (! extension_loaded('curl')) {
            return new Warning('Check\CurlHttpService requires cURL extension to be loaded.');
        }
        // @codeCoverageIgnoreEnd

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt_array($ch, $this->options);

        $result = curl_exec($ch);
        if ($result === false) {
            return new Failure(sprintf('Failed making request to %s', $this->url));
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        if ($this->statusCode !== $statusCode) {
            return new Failure(sprintf(
                'Status code %s does not match %s in response from %s',
                $this->statusCode,
                $statusCode,
                $this->url
            ));
        }

        if ($this->content && (is_string($result) === false || strpos($result, $this->content) === false)) {
            return new Failure(sprintf(
                'Content %s not found in response from %s',
                $this->content,
                $this->url
            ));
        }

        return new Success();
    }
}
