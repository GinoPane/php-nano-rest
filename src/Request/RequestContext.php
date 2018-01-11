<?php

namespace GinoPane\NanoRest\Request;

use GinoPane\NanoRest\Supplemental\HeadersProperty;
use GinoPane\NanoRest\Response\ResponseContextAbstract;
use GinoPane\NanoRest\Exceptions\RequestContextException;

/**
 * Class RequestContext
 *
 * @author Sergey <Gino Pane> Karavay
 */
class RequestContext
{
    /**
     * Default values for timeouts
     */
    const TIMEOUT_DEFAULT               = 10;
    const CONNECTION_TIMEOUT_DEFAULT    = 5;

    /**
     * Default values for charsets
     */
    const CHARSET_UTF8      = 'UTF-8';
    const CHARSET_ISO88591  = 'ISO-8859-1';

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
    const CONTENT_TYPE_FORM         = 'multipart/form-data';
    const CONTENT_TYPE_FORM_URLENCODED  = 'application/x-www-form-urlencoded';
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
    private $contentType = self::CONTENT_TYPE_TEXT_PLAIN;

    /**
     * Default charset for requests
     *
     * @var string
     */
    private $charset = self::CHARSET_UTF8;

    /**
     * Preferred HTTP method
     *
     * @var string
     */
    private $method = self::METHOD_GET;

    /**
     * Generic data to be sent
     *
     * @var mixed
     */
    private $data = null;

    /**
     * Parameters that should be appended to request URL
     *
     * @var array
     */
    private $requestParameters = [];

    /**
     * Options for transport
     *
     * @var array
     */
    private $curlOptions = [];

    /**
     * URL string for request
     *
     * @var string
     */
    private $url = '';

    /**
     * Address of proxy server
     *
     * @var string
     */
    private $proxy = '';

    /**
     * Connection timeout
     *
     * @var int
     */
    private $connectionTimeout = self::CONNECTION_TIMEOUT_DEFAULT;

    /**
     * General timeout value to be used with the request
     *
     * @var
     */
    private $timeout = self::TIMEOUT_DEFAULT;

    /**
     * The name of the class of desired ResponseContext
     *
     * @var string
     */
    private $responseContextClass = '';

    use HeadersProperty;
    use HttpBuildQueryBehavior;

    /**
     * RequestContext constructor
     *
     * @param string $url
     *
     * @throws RequestContextException
     */
    public function __construct(string $url = '')
    {
        if ($url) {
            $this->setUrl($url);
        }
    }

    /**
     * Fluent setter for consistency with other methods
     *
     * @param array $headers
     *
     * @return RequestContext
     */
    public function setHeaders(array $headers = []): RequestContext
    {
        $this->headers()->setHeaders($headers);

        return $this;
    }

    /**
     * Get headers prepared for request with Content-type assigned if it was not already set
     *
     * @return array
     */
    public function getRequestHeaders(): array
    {
        $headers = clone $this->headers();

        if (!$headers->headerExists('Content-type')) {
            if ($contentType = $this->getContentType()) {
                if (($charset = $this->getCharset()) && (stripos($contentType, 'charset=') === false)) {
                    $contentType .= "; charset={$charset}";
                }

                $headers->setHeader('Content-type', $contentType);
            }
        }

        return $headers->getHeadersForRequest();
    }

    /**
     * Set data for request
     *
     * @param mixed $data
     *
     * @return RequestContext
     */
    public function setData($data): RequestContext
    {
        $this->data = $data;

        return $this;
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
     * Get previously set data encoded for request
     *
     * @return string
     */
    public function getRequestData(): string
    {
        $requestData = $this->getData();

        $requestData = is_array($requestData) ? $this->httpBuildQuery($requestData) : (string)$requestData;

        return $requestData;
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Override default HTTP method
     *
     * @param string $method
     *
     * @throws RequestContextException
     *
     * @return RequestContext
     */
    public function setMethod(string $method): RequestContext
    {
        $method = strtoupper($method);

        if (!in_array($method, self::$availableMethods)) {
            throw new RequestContextException('Supplied HTTP method is not supported');
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Get URL string
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set URL string
     *
     * @param string $url
     *
     * @throws RequestContextException
     *
     * @return RequestContext
     */
    public function setUrl(string $url): RequestContext
    {
        $this->assertValidUrl($url);

        $this->url = $url;

        return $this;
    }

    /**
     * Get URL string with request parameters applied
     *
     * @return string
     */
    public function getRequestUrl(): string
    {
        $url = $this->getUrl();

        if ($this->getRequestParameters()) {
            $url = $this->attachQueryToUrl($url, $this->httpBuildQuery($this->getRequestParameters()));
        }

        return $url;
    }

    /**
     * Attach request query to URL
     *
     * @param $url
     * @param $query
     *
     * @return string
     */
    public function attachQueryToUrl($url, $query): string
    {
        return $url . (strpos($url, '?') === false ? '?' : '') . $query;
    }

    /**
     * Get request params
     *
     * @return array
     */
    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }

    /**
     * Set an array of request params
     *
     * @param array $requestParameters
     *
     * @return RequestContext
     */
    public function setRequestParameters(array $requestParameters = []): RequestContext
    {
        $this->requestParameters = $requestParameters;

        return $this;
    }

    /**
     * Get cURL options
     *
     * @return array
     */
    public function getCurlOptions(): array
    {
        return $this->curlOptions;
    }

    /**
     * Set a single CURL option for context
     *
     * @param int $optionName
     * @param mixed $optionValue
     *
     * @throws RequestContextException
     *
     * @return RequestContext
     */
    public function setCurlOption(int $optionName, $optionValue): RequestContext
    {
        if (@curl_setopt(curl_init(), $optionName, $optionValue)) {
            $this->curlOptions[$optionName] = $optionValue;
        } else {
            throw new RequestContextException(
                "Curl option is invalid: '$optionName' => " . var_export($optionValue, true)
            );
        }

        return $this;
    }

    /**
     * Set an array of CURL options for context. Please note, that old options would be removed or overwritten
     *
     * @param array $curlOptions
     *
     * @throws RequestContextException
     *
     * @return RequestContext
     */
    public function setCurlOptions(array $curlOptions = []): RequestContext
    {
        $this->curlOptions = [];

        foreach ($curlOptions as $name => $value) {
            $this->setCurlOption($name, $value);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     *
     * @return RequestContext
     */
    public function setContentType($contentType): RequestContext
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get charset for current request
     *
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Set charset for current request
     *
     * @param string $charset
     *
     * @return RequestContext
     */
    public function setCharset(string $charset): RequestContext
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @return string
     */
    public function getProxy(): string
    {
        return $this->proxy;
    }

    /**
     * @param string $proxy
     *
     * @throws RequestContextException
     *
     * @return RequestContext
     */
    public function setProxy(string $proxy): RequestContext
    {
        $this->assertValidUrl($proxy);

        $this->proxy = $proxy;

        return $this;
    }

    /**
     * @return int|float
     */
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }

    /**
     * @param int|float $connectionTimeout
     *
     * @return RequestContext
     */
    public function setConnectionTimeout($connectionTimeout): RequestContext
    {
        $this->connectionTimeout = $connectionTimeout;

        return $this;
    }

    /**
     * @return int|float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int|float $timeout
     *
     * @return RequestContext
     */
    public function setTimeout($timeout): RequestContext
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Sets desired type of response context
     *
     * @param string $responseContextClass
     *
     * @throws RequestContextException
     *
     * @return RequestContext
     */
    public function setResponseContextClass(string $responseContextClass): RequestContext
    {
        if (!is_a($responseContextClass, ResponseContextAbstract::class, true)) {
            throw new RequestContextException(
                sprintf(
                    "Class %s must have %s as one of its parents",
                    $responseContextClass, //@codeCoverageIgnore
                    ResponseContextAbstract::class
                )
            );
        }

        $this->responseContextClass = $responseContextClass;

        return $this;
    }

    /**
     * Returns current response context object of desired type
     *
     * @return ResponseContextAbstract
     */
    public function getResponseContextObject(): ResponseContextAbstract
    {
        return $this->responseContextClass ? new $this->responseContextClass() : ResponseContextAbstract::getByType('');
    }

    /**
     * Get string representation of RequestContext object
     *
     * @return string
     */
    public function __toString(): string
    {
        $headers = $this->getRequestHeaders()
            ? print_r($this->getRequestHeaders(), true)
            : "No headers were set";

        $data = $this->getData() ? print_r($this->getData(), true) : "No data was set";

        $requestParameters = $this->getRequestParameters()
            ? print_r($this->getRequestParameters(), true)
            : "No request parameters were set";

        return <<<DEBUG
===================
Method: {$this->getMethod()}
Request URL: {$this->getRequestUrl()}
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

    /**
     * Throw exception on invalid URL
     *
     * @param string $url
     *
     * @throws RequestContextException
     */
    private function assertValidUrl(string $url): void
    {
        if (!(filter_var($url, FILTER_VALIDATE_URL) || filter_var($url, FILTER_VALIDATE_IP))) {
            throw new RequestContextException("Failed to set invalid URL: $url");
        }
    }
}
