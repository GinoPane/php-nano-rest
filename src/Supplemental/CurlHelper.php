<?php

namespace GinoPane\NanoRest\Supplemental;

use GinoPane\NanoRest\{
    Request\RequestContext,
    Exceptions\TransportException
};

class CurlHelper
{
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

        $defaults = [
            CURLOPT_ENCODING        => "",
            CURLOPT_USERAGENT       => "php-nano-rest",
            CURLOPT_HEADER          => true,
            CURLOPT_HTTPHEADER      => array_values($context->getRequestHeaders()),
            CURLOPT_RETURNTRANSFER  => true,
        ];

        $defaults += $this->getCurlTimeoutSettings($context);

        $defaults += $this->getCurlSslSettings();

        if (!is_null($context->getProxy())) {
            $defaults[CURLOPT_PROXY] = $context->getProxy();
        }

        $dataAndMethodOptions = $this->getRequestDataAndMethodOptions($context);

        curl_setopt_array($curlHandle, $context->getCurlOptions() + $defaults + $dataAndMethodOptions);

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

        @list($headers, $response) = explode("\r\n\r\n", curl_exec($curlHandle), 2);

        $error = curl_error($curlHandle);
        $errorNumber = curl_errno($curlHandle);
        $httpStatus = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

        curl_close($curlHandle);

        $this->handleCurlError($error, $errorNumber, $verbose);

        return [
            'response' => $response,
            'httpStatus' => $httpStatus,
            'headers' => $headers
        ];
    }

    /**
     * Get SSL settings for CURL handler
     *
     * @link https://curl.haxx.se/docs/caextract.html
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
     * Get timeout settings for CURL handler
     *
     * @link https://curl.haxx.se/libcurl/c/CURLOPT_TIMEOUT_MS.html
     * @link https://curl.haxx.se/libcurl/c/CURLOPT_CONNECTTIMEOUT_MS.html
     *
     * @param RequestContext $context
     *
     * @return array
     */
    private function getCurlTimeoutSettings(RequestContext $context): array
    {
        $timeoutOptions = [];

        $timeout = $context->getTimeout();

        if (is_int($timeout)) {
            $timeoutOptions[CURLOPT_TIMEOUT] = $timeout;
        } elseif (is_float($timeout)) {
            $timeoutOptions[CURLOPT_TIMEOUT_MS] = $timeout * 1000;
            $timeoutOptions[CURLOPT_NOSIGNAL] = 1;
        }

        $connectionTimeout = $context->getConnectionTimeout();

        if (is_int($connectionTimeout)) {
            $timeoutOptions[CURLOPT_CONNECTTIMEOUT] = $connectionTimeout;
        } elseif (is_float($connectionTimeout)) {
            $timeoutOptions[CURLOPT_CONNECTTIMEOUT_MS] = $connectionTimeout * 1000;
            $timeoutOptions[CURLOPT_NOSIGNAL] = 1;
        }

        return $timeoutOptions;
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

    /**
     * @param $error
     * @param $errorNumber
     * @param $verbose
     *
     * @throws TransportException
     */
    private function handleCurlError($error, $errorNumber, $verbose): void
    {
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
    }
}
