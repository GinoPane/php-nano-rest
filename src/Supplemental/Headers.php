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
    /**
     * Array of stored headers
     *
     * @var array
     */
    private $headers = [];

    /**
     * Headers constructor
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        $this->setHeaders($headers);
    }

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
        $this->headers[self::processKey($header)] = $content;

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
            $this->setHeader((string)$header, (string)$data);
        }

        return $this;
    }

    /**
     * Set headers from headers string
     *
     * @param string $headers
     *
     * @return Headers
     */
    public function setHeadersFromString(string $headers): Headers
    {
        return $this->setHeaders($this::parseHeaders($headers));
    }

    /**
     * Get header value by name
     *
     * @param string $key
     *
     * @return null|string
     */
    public function getHeader(string $key): ?string
    {
        $key = self::processKey($key);

        return isset($this->headers[$key]) ? $this->headers[$key] : null;
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

    /**
     * Returns an array of "name: value" pair for request
     *
     * @return array
     */
    public function getHeadersForRequest(): array
    {
        $headers = $this->headers;

        array_walk($headers, function (&$value, $name) {
            $value = "$name: $value";
        });

        return $headers;
    }

    /**
     * Returns true if headers exists, false otherwise
     *
     * @param string $header
     *
     * @return bool
     */
    public function headerExists(string $header): bool
    {
        return array_key_exists(self::processKey($header), $this->getHeaders());
    }

    /**
     * Parse headers string into associative array. Only named headers returned
     *
     * Duplicated headers are parsed into comma-separated string:
     * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
     * @link https://stackoverflow.com/questions/4371328/are-duplicate-http-response-headers-acceptable
     * @link https://stackoverflow.com/questions/6368574/how-to-get-the-functionality-of-http-parse-headers-without-pecl
     *
     * @param string $headers
     *
     * @return array
     */
    public static function parseHeaders(string $headers): array
    {
        $parsedHeaders = array();

        foreach (explode("\n", $headers) as $header) {
            @list($headerTitle, $headerValue) = explode(':', $header, 2);

            if (isset($headerValue)) {
                $headerTitle = self::processKey(trim($headerTitle));
                $headerValue = trim($headerValue);

                if (!isset($parsedHeaders[$headerTitle])) {
                    $parsedHeaders[$headerTitle] = $headerValue;
                } else {
                    $parsedHeaders[$headerTitle] .= ", $headerValue";
                }
            }
        }

        return $parsedHeaders;
    }

    /**
     * Helper method to statically create Headers object from headers string
     *
     * @param string $headers
     *
     * @return Headers
     */
    public static function createFromString(string $headers): Headers
    {
        return new self(Headers::parseHeaders($headers));
    }

    /**
     * Just returns processed (lower-cased) version of the $key
     *
     * @param string $key
     * @return string
     */
    private static function processKey(string $key): string
    {
        return strtolower($key);
    }
}
