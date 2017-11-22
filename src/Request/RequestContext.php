<?php

namespace GinoPane\NanoRest\Request;

use GinoPane\NanoRest\Exceptions\RequestContextException;

/**
 * Class RequestContext
 *
 * @package GinoPane\NanoRest\Request
 */
class RequestContext
{
    /**
     * Sample HTTP Methods
     */
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_PATCH   = 'PATCH';

    /**
     * Sample content types
     */
    const CONTENT_TYPE_TEXT_PLAIN   = 'text/plain';
    const CONTENT_TYPE_JSON         = 'application/json';
    const CONTENT_TYPE_JAVASCRIPT   = 'application/javascript';
    const CONTENT_TYPE_APP_XML      = 'application/xml';
    const CONTENT_TYPE_TEXT_XML     = 'text/xml';
    const CONTENT_TYPE_TEXT_HTML    = 'text/html';

    /**
     * The list of supported HTTP methods
     *
     * @var array
     */
    private static $availableMethods = array(
         self::METHOD_OPTIONS,
         self::METHOD_GET,
         self::METHOD_HEAD,
         self::METHOD_POST,
         self::METHOD_PUT,
         self::METHOD_DELETE,
         self::METHOD_TRACE,
         self::METHOD_CONNECT,
         self::METHOD_PATCH
    );

    /**
     * Default content type for requests
     */
    protected $contentType = "text/xml";

    /**
     * Default charset for requests
     *
     * @var string
     */
    protected $charset = "utf-8";

    /**
     * Preferred HTTP method
     *
     * @var string
     */
    protected $method = self::METHOD_POST;

    /**
     * List of headers for a request
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Generic data to be sent
     *
     * @var mixed
     */
    protected $data = null;

    /**
     * Parameters that should be appended to request URI
     *
     * @var array
     */
    protected $requestParams = array();

    /**
     * Options for transport
     *
     * @var array
     */
    protected $transportOptions = array();

    /**
     * URI string for request
     *
     * @var string
     */
    protected $uri = '';

    /**
     * Enable/disable sandbox mode for the request. Can be used
     *
     * @var bool
     */
    protected $sandboxMode = false;

    /**
     * Options for sandbox mode
     *
     * @var array
     */
    protected $sandboxOptions = array();

    /**
     * RequestContext constructor
     *
     * @param array $options Available keys are:
     *  uri,
     *  headers,
     *  data,
     *  method
     */
    public function __construct(array $options = array())
    {
        $uri = '';
        $headers = array();
        $data = null;
        $requestParams = array();
        $method = $this->method;
        $transportOptions = array();

        extract($options, EXTR_IF_EXISTS | EXTR_OVERWRITE);

        $this->setUri($uri);
        $this->setHeaders($headers);
        $this->setData($data);
        $this->setRequestParameters($requestParams);
        $this->setTransportOptions($transportOptions);

        if ($method) {
            $this->setMethod($method);
        }
    }

    /**
     * Set individual header
     *
     * @param $header
     * @param $data
     */
    public function setHeader($header, $data)
    {
        $this->headers[$header] = "{$header}: $data";
    }

    /**
     * Set headers array
     *
     * @param array $headers Array of header -> data pairs
     */
    public function setHeaders(array $headers = array())
    {
        $this->headers = array();

        foreach ($headers as $header => $data) {
            $this->setHeader($header, $data);
        }
    }

    /**
     * Get all set headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get previously set data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data for request
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Override default HTTP method
     *
     * @param string $method
     *
     * @throws RequestContextException
     */
    public function setMethod($method)
    {
        if (!in_array($method, self::$availableMethods)) {
            throw new RequestContextException('Supplied HTTP method is not supported');
        }

        $this->method = $method;
    }

    /**
     * Get URI string
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set URI string
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get URI string with parameters applied
     *
     * @return string
     */
    public function getRequestUri()
    {
        $uri = $this->getUri();

        if ($this->getRequestParameters()) {
            $uri .= (strpos($uri, '?') === false ? '?' : '') . http_build_query($this->getRequestParameters());
        }

        return $uri;
    }

    /**
     * Get request params
     *
     * @return array
     */
    public function getRequestParameters()
    {
        return $this->requestParams;
    }

    /**
     * Set an array of request params
     *
     * @param array $requestParams
     */
    public function setRequestParameters(array $requestParams = array())
    {
        $this->requestParams = $requestParams;
    }

    /**
     * Get transport options
     *
     * @return array
     */
    public function getTransportOptions()
    {
        return $this->transportOptions;
    }

    /**
     * Set an array of transport options for context
     *
     * @param array $transportOptions
     */
    public function setTransportOptions(array $transportOptions)
    {
        $this->transportOptions = $transportOptions;
    }

    /**
     * Set a single transport option for context
     *
     * @param $optionName
     * @param $optionValue
     */
    public function setTransportOption($optionName, $optionValue)
    {
        $this->transportOptions[$optionName] = $optionValue;
    }

    /**
     * Sets sandboxMode to 'true'/'false' respectively to passed $mode argument
     *
     * @param $mode
     */
    public function setSandboxMode($mode)
    {
        $this->sandboxMode = (bool)$mode;
    }

    /**
     * @return bool
     */
    public function getSandboxMode()
    {
        return $this->sandboxMode;
    }

    /**
     * @return array
     */
    public function getSandboxOptions()
    {
        return $this->sandboxOptions;
    }

    /**
     * @param array $sandboxOptions
     */
    public function setSandboxOptions(array $sandboxOptions)
    {
        $this->sandboxOptions = $sandboxOptions;
    }

    /**
     * Get string representation of an object
     *
     * @return string
     */
    public function __toString()
    {
        $headers = $this->getHeaders() ? print_r($this->getHeaders(), 1) : "No headers were set";
        $data = $this->getData() ? print_r($this->getData(), 1) : "No data was set";
        $requestParameters = $this->getRequestParameters() ? print_r($this->getRequestParameters(), 1) : "No request parameters were set";

        return <<<DEBUG
        
===================        
Method: {$this->getMethod()}
URI: {$this->getRequestUri()}
===================
Headers:

{$headers}
===================
Data:

{$data}
===================
Request Parameters:

{$requestParameters}
===================

DEBUG;
    }
}