<?php

namespace GinoPane\NanoRest\Supplemental;

/**
 * Class Headers
 *
 * Simple container object to store request and response headers
 *
 * @package GinoPane\NanoRest\Supplemental
 */
class Headers
{
    private $headers = [];

    /**
     * Set individual header
     *
     * @param string $header
     * @param string $content
     *
     * @return Headers
     */
    public function setHeader(string $header, string $content): Headers
    {
        $this->headers[$header] = "$header: $content";

        return $this;
    }

    /**
     * Set headers array
     *
     * @param array $headers Array of header -> data pairs
     *
     * @return Headers
     */
    public function setHeaders(array $headers = []): Headers
    {
        $this->headers = array();

        foreach ($headers as $header => $data) {
            $this->setHeader($header, $data);
        }

        return $this;
    }

    /**
     * Get all set headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
