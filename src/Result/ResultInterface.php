<?php

namespace Laminas\Diagnostics\Result;

/**
 * @template T
 */
interface ResultInterface
{
    /**
     * Get message related to the result.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Get detailed info on the test result (if available).
     *
     * @return T|null
     */
    public function getData();
}
