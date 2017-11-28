<?php

namespace GinoPane\NanoRest;

define('ROOT_DIRECTORY', dirname(dirname(__FILE__)));

use GinoPane\NanoRest\{
    Request\RequestContext,
    Response\ResponseContext,
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
     * Default response context
     *
     * @var ResponseContext
     */
    protected $responseContext = null;

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
        return $this->responseContext ?: ResponseContext::getByType('');
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

        list(
            'httpStatus'    => $status,
            'response'      => $content,
            'headers'       => $headers
        ) = $this->executeRequestHandle($curlHandle);

        $responseContext->setRequestContext($requestContext)
            ->setHttpStatusCode($status)
            ->setContent($content);

        $responseContext->headers()->setHeadersFromString($headers);

        return $responseContext;
    }

    /**
     * Get a customized request handler to perform calls
     *
     * @param RequestContext $context
     *
     * @return resource
     */
    private function getRequestHandle(RequestContext $context)
    {
        $curlHandle = curl_init();

        $defaults = [
            CURLOPT_ENCODING        => "",
            CURLOPT_USERAGENT       => "php-nano-rest",
            CURLOPT_HEADER          => true,
            CURLOPT_HTTPHEADER      => array_values($context->getRequestHeaders()),
            CURLOPT_RETURNTRANSFER  => true,

            CURLOPT_NOSIGNAL        => 1,
            CURLOPT_CONNECTTIMEOUT  => $context->getConnectionTimeout(),
            CURLOPT_TIMEOUT         => $context->getTimeout(),
        ];

        $defaults += $this->getCurlSslSettings();

        if (!is_null($context->getProxy())) {
            $defaults[CURLOPT_PROXY] = $context->getProxy();
        }

        $dataAndMethodOptions = $this->getRequestDataAndMethodOptions($context);

        curl_setopt_array($curlHandle, $context->getCurlOptions() + $defaults + $dataAndMethodOptions);

        return $curlHandle;
    }

    /**
     * Get SSL settings for CURL handler
     *
     * @return array
     */
    private function getCurlSslSettings(): array
    {
        return [
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_SSL_VERIFYHOST  => 2,
            CURLOPT_CAINFO          => ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cacert.pem'
        ];
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
    private function executeRequestHandle($curlHandle)
    {
        curl_setopt($curlHandle, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curlHandle, CURLOPT_STDERR, $verbose);

        @list($headers, $response) = explode("\r\n\r\n", curl_exec($curlHandle), 2);

        $error = curl_error($curlHandle);
        $errorNumber = curl_errno($curlHandle);
        $httpStatus = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

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

        return ['response' => $response, 'httpStatus' => $httpStatus, 'headers' => $headers];
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

        $url = $context->getRequestUri();

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

        $curlOptions[CURLOPT_URL] = $url;

        return $curlOptions;
    }
}
