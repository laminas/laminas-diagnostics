<?php

namespace Laminas\Diagnostics\Result;

/**
 * Abstract, simple implementation of ResultInterface
 */
abstract class AbstractResult implements ResultInterface
{
    /** @var string */
    protected $message;

    /** @var mixed|null */
    protected $data;

    /**
     * Create new result
     *
     * @param string      $message
     * @param mixed|null  $data
     */
    public function __construct($message = '', $data = null)
    {
        $this->setMessage($message);

        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * Get message related to the result.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get detailed info on the test result (if available).
     *
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed|null $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
