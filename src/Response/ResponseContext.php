<?php

namespace GinoPane\NanoRest\Response;

use GinoPane\NanoRest\{
    Exceptions\ResponseContextException, Request\RequestContext, Supplemental\HeadersProperty
};

/**
 * Class ResponseContext
 *
 * @package GinoPane\NanoRest\Response
 */
abstract class ResponseContext
{
    /**
     * Integer part of possible HTTP error codes (4xx, 5xx)
     *
     * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_errors
     * @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#5xx_Server_errors
     *
     * @var array
     */
    private static $errorCodesRange = array(4, 5);

    /**
     * Constant for JSON response type
     */
    const RESPONSE_TYPE_JSON = "JSON";

    /**
     * Stored raw content
     *
     * @var string|null
     */
    protected $content = null;

    /**
     * Response status code
     *
     * @var int
     */
    protected $httpStatusCode;

    /**
     * Request context that was used for request
     *
     * @var RequestContext
     */
    protected $requestContext = null;

    /**
     * Get raw result data
     *
     * @param array $options
     *
     * @return string
     */
    abstract public function getRaw(array $options = array()): string;

    /**
     * Get result data as array
     *
     * @param array $options
     * @return array
     */
    abstract public function getArray(array $options = array()): array;

    /**
     * Get result data as object
     *
     * @param array $options
     */
    abstract public function getObject(array $options = array());

    /**
     * Checks whether content is valid for the result.
     *
     * @param string $content
     * @param string $error Error is returned here if any
     *
     * @return bool
     */
    abstract public function isValid(string $content, string &$error);

    /**
     * String representation of response for debug purposes
     *
     * @return string
     */
    abstract public function __toString();

    use HeadersProperty;

    /**
     * ResponseContext constructor
     *
     * @param string|null $content
     */
    public function __construct(string $content = null)
    {
        if (!is_null($content)) {
            $this->setContent($content);
        }
    }

    /**
     * Set result content.
     *
     * @param string $content
     *
     * @return ResponseContext
     */
    public function setContent(string $content): ResponseContext
    {
        $this->assert($content);

        $this->content = $content;

        return $this;
    }

    /**
     * Get result's content.
     *
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get HTTP status code from response
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Set HTTP status code for response
     *
     * @param int|string $httpStatusCode
     *
     * @return ResponseContext
     */
    public function setHttpStatusCode($httpStatusCode): ResponseContext
    {
        $this->httpStatusCode = (int)$httpStatusCode;

        return $this;
    }

    /**
     * Set request context that is being used for request
     *
     * @param RequestContext $request
     *
     * @return ResponseContext
     */
    public function setRequestContext(RequestContext $request): ResponseContext
    {
        $this->requestContext = $request;

        return $this;
    }

    /**
     * Get request context for this request
     *
     * @return RequestContext
     */
    public function getRequestContext()
    {
        return $this->requestContext ?: new RequestContext();
    }

    /**
     * Returns if current response context has HTTP error status code set
     *
     * @return bool
     */
    public function hasHttpError(): bool
    {
        if (in_array(floor($this->httpStatusCode / 100), self::$errorCodesRange)) {
            return true;
        }

        return false;
    }

    /**
     * Makes sure that $content is valid for this AbstractResponseContext instance
     *
     * @param string $content
     *
     * @throws ResponseContextException
     */
    protected function assert(string $content): void
    {
        $error = '';

        if (!$this->isValid($content, $error)) {
            throw new ResponseContextException($error ?: '');
        }
    }

    /**
     * Create response context instance
     *
     * @param string $type
     *
     * @return ResponseContext
     */
    public static function getByType(string $type): ResponseContext
    {
        switch ($type) {
            case self::RESPONSE_TYPE_JSON:
                return new JsonResponseContext();
            default:
                return new DummyResponseContext();
        }
    }
}
