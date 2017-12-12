<?php

namespace GinoPane\NanoRest\Response;

use GinoPane\NanoHttpStatus\NanoHttpStatus;

use GinoPane\NanoRest\{
    Exceptions\ResponseContextException, Request\RequestContext, Supplemental\HeadersProperty
};

/**
 * Class ResponseContext
 *
 */
abstract class ResponseContext
{
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
     * @return string|null
     */
    abstract public function getRaw(array $options = array()): ?string;

    /**
     * Get result data as array
     *
     * @param array $options
     *
     * @return array
     */
    abstract public function getArray(array $options = array()): array;

    /**
     * Get result data as object
     *
     * @param array $options
     *
     * @return object|null
     */
    abstract public function getObject(array $options = array());

    /**
     * Makes sure that $content is valid for this AbstractResponseContext instance
     *
     * @param string $content
     *
     * @throws ResponseContextException
     *
     * @return bool
     */
    abstract protected function assertIsValid(string $content): bool;

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
        $this->assertIsValid($content);

        $this->content = $content;

        return $this;
    }

    /**
     * Get response content
     *
     * @return null|string
     */
    public function getContent(): ?string
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
     * Get HTTP status message from response
     *
     * @return string
     */
    public function getHttpStatusMessage(): string
    {
        return (new NanoHttpStatus())->getMessage($this->httpStatusCode);
    }
    /**
     * Set HTTP status code for response
     *
     * @param int|string $httpStatusCode
     *
     * @return ResponseContext
     */
    public function setHttpStatusCode(int $httpStatusCode): ResponseContext
    {
        $this->httpStatusCode = $httpStatusCode;

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
        $httpStatus = new NanoHttpStatus();

        return $httpStatus->isClientError($this->httpStatusCode) || $httpStatus->isServerError($this->httpStatusCode);
    }

    /**
     * Create response context instance
     *
     * @param string $type
     * @param string|null $content
     *
     * @return ResponseContext
     */
    public static function getByType(string $type, string $content = null): ResponseContext
    {
        switch ($type) {
            case self::RESPONSE_TYPE_JSON:
                return new JsonResponseContext($content);
            default:
                return new DummyResponseContext($content);
        }
    }
}
