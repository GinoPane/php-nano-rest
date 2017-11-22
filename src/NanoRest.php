<?php
/**
 *
 */

namespace GinoPane\NanoRest;

use GinoPane\NanoRest\{
    Request\RequestContext,
    Response\ResponseContext,
    Response\DummyResponseContext,
    Exceptions\TransportException
};

/**
 * Class NanoRest
 *
 * Abstract implementation of transport layer
 *
 * @package GinoPane\NanoRest
 * @author Sergey <Gino Pane> Karavay
 */
class NanoRest
{
    /**
     * Address of proxy server
     *
     * @var string
     */
    protected $proxy = '';

    /**
     * URL prefix
     *
     * @var string
     */
    protected $proxyScript = '';

    /**
     * Connection timeout
     *
     * @var int
     */
    protected $connectionTimeout = 0;

    /**
     * General timeout value to be used with the request
     *
     * @var
     */
    protected $timeout = 0;

    /**
     * Default request context
     *
     * @var RequestContext
     */
    protected $requestContext = null;

    /**
     * Default response context
     *
     * @var ResponseContext
     */
    protected $responseContext = null;

    /**
     * NanoRest constructor
     *
     * @param array $options    Array of options to be set.
     *                          Supported keys are: 'proxy', 'proxyScript', 'connectionTimeout'
     */
    public function __construct(array $options = array())
    {
        $proxy = '';
        $proxyScript = '';
        $connectionTimeout = 0;

        extract($options, EXTR_IF_EXISTS | EXTR_OVERWRITE);

        $this->proxy = $proxy;
        $this->proxyScript = $proxyScript;
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * Sets current request context
     *
     * @param RequestContext $context
     *
     * @return $this
     */
    public function setRequestContext(RequestContext $context)
    {
        $this->requestContext = $context;

        return $this;
    }

    /**
     * Returns current request context
     *
     * @return RequestContext
     */
    public function getRequestContext()
    {
        return $this->requestContext ?: new RequestContext();
    }

    /**
     * Sets current request context
     *
     * @param ResponseContext $context
     *
     * @return $this
     */
    public function setResponseContext(ResponseContext $context)
    {
        $this->responseContext = $context;

        return $this;
    }

    /**
     * Returns current request context
     *
     * @return ResponseContext
     */
    public function getResponseContext()
    {
        return $this->responseContext ?: new DummyResponseContext();
    }

    /**
     * Send previously prepared request
     *
     * @param RequestContext $requestContext
     * @param ResponseContext $responseContext
     *
     * @return ResponseContext
     */
    public function sendRequest(RequestContext $requestContext, ResponseContext $responseContext = null)
    {
        $curlHandle = $this->getRequestHandle($requestContext);

        if (!$responseContext) {
            $responseContext = $this->getResponseContext();
        }

        $responseContext->setContent($this->executeRequestHandle($curlHandle));

        return $responseContext;
    }

    /**
     * Get a customized request handler to perform calls
     *
     * @param RequestContext $context
     *
     * @return resource
     */
    public function getRequestHandle(RequestContext $context)
    {
        $curlHandle = curl_init();
        $transportOptions = $this->processTransportOptions($context);

        $defaults = array(
            CURLOPT_HEADER          => true,
            CURLOPT_HTTPHEADER      => array_values($context->getHeaders() + $this->requestContext->getHeaders()),
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_CONNECTTIMEOUT  => $this->connectionTimeout,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => $this->timeout
        );

        if (!is_null($this->proxy)) {
            $defaults[CURLOPT_PROXY] = $this->proxy;
        }

        $dataAndMethodOptions = $this->getRequestDataAndMethodOptions($context);

        curl_setopt_array($curlHandle, $transportOptions + $defaults + $dataAndMethodOptions);

        return $curlHandle;
    }

    /**
     * Execute curl handle
     *
     * @param resource $curlHandle
     *
     * @throws TransportException
     *
     * @return mixed
     */
    public function executeRequestHandle($curlHandle)
    {
        curl_setopt($curlHandle, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curlHandle, CURLOPT_STDERR, $verbose);

        list($headers, $response) = explode("\r\n\r\n", curl_exec($curlHandle), 2);
        $error = curl_error($curlHandle);
        $errorNumber = curl_errno($curlHandle);

        curl_close($curlHandle);

        if ($error) {
            $errorMessage = "\ncURL transport error: $errorNumber - $error";

            $transportException = new TransportException($errorMessage);

            if (!empty($verbose)) {
                rewind($verbose);
                $verboseLog = htmlspecialchars(stream_get_contents($verbose));

                if ($verboseLog) {
                    $transportException->setData($verboseLog);
                }
            }

            throw $transportException;
        }

        return $response;
    }

    /**
     * Extracts transport options from request context and returns an array with options left unprocessed
     *
     * @param RequestContext $context
     *
     * @return array
     */
    private function processTransportOptions(RequestContext $context)
    {
        $transportOptions = $context->getTransportOptions();

        $this->timeout = isset($transportOptions['timeout']) ? $transportOptions['timeout'] : $this->timeout;
        $this->connectionTimeout = isset($transportOptions['connectionTimeout'])
            ? $transportOptions['connectionTimeout']
            : $this->connectionTimeout;

        unset($transportOptions['timeout']);
        unset($transportOptions['connectionTimeout']);

        return $transportOptions;
    }

    /**
     * @param RequestContext $context
     *
     * @return array
     */
    private function getRequestDataAndMethodOptions(RequestContext $context)
    {
        $curlOptions = array();

        $requestData = $context->getData();
        $requestData = is_array($requestData) ? http_build_query($requestData) : $requestData;

        $url = $this->proxyScript
            ? $this->proxyScript . urlencode($context->getRequestUri())
            : $context->getRequestUri();

        if ($context->getMethod()) {
            switch ($context->getMethod()) {
                case RequestContext::METHOD_GET:
                    $curlOptions[CURLOPT_HTTPGET] = 1;
                    $url .= (strpos($url, '?') === false ? '?' : '') . $requestData;
                    break;
                case RequestContext::METHOD_POST:
                    $curlOptions[CURLOPT_POST] = 1;
                    $curlOptions[CURLOPT_POSTFIELDS] = $requestData;
                    break;
                default:
                    $curlOptions[CURLOPT_CUSTOMREQUEST] = $context->getMethod();
                    $curlOptions[CURLOPT_POSTFIELDS] = $requestData;
            }
        } else {
            //If method is unknown, but we have data to send, then just send everything via POST
            if ($requestData) {
                $curlOptions[CURLOPT_POST] = 1;
                $curlOptions[CURLOPT_POSTFIELDS] = $requestData;
            }
        }

        $curlOptions[CURLOPT_URL] = $url;

        return $curlOptions;
    }
}
