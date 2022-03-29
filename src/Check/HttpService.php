<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

use function fclose;
use function feof;
use function fgets;
use function fsockopen;
use function fwrite;
use function preg_match;
use function sprintf;
use function strpos;

/**
 * Attempt connection to given HTTP host and (optionally) check status code and page content.
 */
class HttpService extends AbstractCheck
{
    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var string */
    protected $path;

    /** @var int */
    protected $statusCode;

    /** @var int */
    protected $content;

    /**
     * @param string $host       Host name or IP address to check.
     * @param int    $port       Port to connect to (defaults to 80)
     * @param string $path       The path to retrieve (defaults to /)
     * @param int    $statusCode (optional) Expected status code
     * @param null   $content    (optional) Expected substring to match against the page content.
     */
    public function __construct($host, $port = 80, $path = '/', $statusCode = null, $content = null)
    {
        $this->host       = $host;
        $this->port       = $port;
        $this->path       = $path;
        $this->statusCode = $statusCode;
        $this->content    = $content;
    }

    /**
     * @see Laminas\Diagnostics\CheckInterface::check()
     *
     * @return ResultInterface
     */
    public function check()
    {
        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if (! $fp) {
            return new Failure(sprintf(
                'No http service running at host %s on port %s',
                $this->host,
                $this->port
            ));
        }

        $header  = "GET {$this->path} HTTP/1.0\r\n";
        $header .= "Host: {$this->host}\r\n";
        $header .= "Connection: close\r\n\r\n";
        fwrite($fp, $header);
        $str = '';
        while (! feof($fp)) {
            $str .= fgets($fp, 1024);
        }
        fclose($fp);

        if ($this->statusCode && ! preg_match("/^HTTP\/[0-9]\.[0-9] {$this->statusCode}/", $str)) {
            return new Failure(sprintf(
                'Status code %d does not match response from %s:%d%s',
                $this->statusCode,
                $this->host,
                $this->port,
                $this->path
            ));
        }

        if ($this->content && strpos($str, $this->content) === false) {
            return new Failure(sprintf(
                'Content %s not found in response from %s:%d%s',
                $this->content,
                $this->host,
                $this->port,
                $this->path
            ));
        }

        return new Success();
    }
}
