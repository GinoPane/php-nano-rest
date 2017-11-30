<?php

namespace GinoPane\NanoRest\Supplemental;

/**
 * Trait HeadersProperty
 */
trait HeadersProperty
{
    /**
     * List of headers for a request
     *
     * @var Headers
     */
    private $headers = null;

    /**
     * Retrieve object's headers
     *
     * @return Headers
     */
    public function headers(): Headers
    {
        return !is_null($this->headers) ? $this->headers : ($this->headers = new Headers());
    }
}
