<?php

namespace GinoPane\NanoRest\Response;

use GinoPane\NanoRest\{
    Exceptions\ResponseContextException,
    Request\RequestContext
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
     * @var mixed
     */
    protected $content;

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
     */
    abstract public function getRaw(array $options = array());

    /**
     * Get result data as array
     *
     * @param array $options
     * @return array
     */
    abstract public function getArray(array $options = array());

    /**
     * Get result data as object
     *
     * @param array $options
     */
    abstract public function getObject(array $options = array());

    /**
     * Checks whether content is valid for the result.
     *
     * @param mixed $content
     * @param string $error Error is returned here if any
     *
     * @return bool
     */
    abstract public function isValid($content, &$error = '');

    /**
     * String representation of response for debug purposes
     *
     * @return string
     */
    abstract public function __toString();

    /**
     * Set result content.
     *
     * @param $content
     * @return mixed
     */
    public function setContent($content)
    {
        $this->assert($content);

        $this->content = $content;
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
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Set HTTP status code for response
     *
     * @param int $httpStatusCode
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * Set request context that is being used for request
     *
     * @param RequestContext $request
     */
    public function setRequestContext(RequestContext $request)
    {
        $this->requestContext = $request;
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
     */
    public function hasHttpError()
    {
        if (in_array(floor($this->httpStatusCode / 100), self::$errorCodesRange)) {
            return true;
        }

        return false;
    }

    /**
     * Makes sure that $content is valid for this AbstractResponseContext instance
     *
     * @param $content
     *
     * @throws ResponseContextException
     */
    protected function assert($content)
    {
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
    public static function getByType($type)
    {
        switch ($type) {
            case self::RESPONSE_TYPE_JSON:
                return new JsonResponseContext();
            default:
                return new DummyResponseContext();
        }
    }
}