<?php

namespace GinoPane\NanoRest\Supplemental;

use GinoPane\NanoRest\{
    Request\RequestContext,
    Exceptions\TransportException
};

/**
 * Class CurlHelper
 */
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

        $defaults = $this->getDefaultSettings($context);

        $defaults += $this->getCurlTimeoutSettings($context);

        $defaults += $this->getCurlSslSettings();

        $defaults += $this->getProxySettings($context);

        curl_setopt_array(
            $curlHandle, //@codeCoverageIgnore
            $context->getCurlOptions() + $defaults + $this->getRequestDataAndMethodOptions($context)
        );

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
     * CA certificate bundle was generated at Wed Jan 17 04:12:05 2018 GMT.
     * CURL_SSL_XXX options has recommended values for production environments
     *
     * @link https://curl.haxx.se/docs/caextract.html
     *
     * @link https://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
     * @link https://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html
     *
     * @return array
     */
    private function getCurlSslSettings(): array
    {
        return [
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_SSL_VERIFYHOST  => 2,
            CURLOPT_CAINFO          => \GinoPane\NanoRest\ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cacert.pem'
        ];
    }

    /**
     * Get proxy settings for cURL handle
     *
     * @param RequestContext $context
     * @return array
     */
    private function getProxySettings(RequestContext $context): array
    {
        $proxy = [];

        if (!is_null($context->getProxy())) {
            $proxy[CURLOPT_PROXY] = $context->getProxy();
        }

        return $proxy;
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

        $timeoutOptions += $this->fillTimeoutOptions($timeout, CURLOPT_TIMEOUT, CURLOPT_TIMEOUT_MS);

        $connectionTimeout = $context->getConnectionTimeout();

        $timeoutOptions += $this->fillTimeoutOptions(
            $connectionTimeout, //@codeCoverageIgnore
            CURLOPT_CONNECTTIMEOUT,
            CURLOPT_CONNECTTIMEOUT_MS
        );

        return $timeoutOptions;
    }

    /**
     * Get defaults settings for cURL handle
     *
     * @param RequestContext $context
     * @return array
     */
    private function getDefaultSettings(RequestContext $context): array
    {
        $defaults = [
            CURLOPT_ENCODING => "",
            CURLOPT_USERAGENT => "php-nano-rest",
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array_values($context->getRequestHeaders()),
            CURLOPT_RETURNTRANSFER => true,
        ];

        return $defaults;
    }

    /**
     * @param RequestContext $context
     *
     * @return array
     */
    private function getRequestDataAndMethodOptions(RequestContext $context)
    {
        $curlOptions = array();

        $requestData = $context->getRequestData();

        $url = $context->getRequestUrl();

        switch ($context->getMethod()) {
            case RequestContext::METHOD_GET:
                $curlOptions[CURLOPT_HTTPGET] = 1;
                $url = $context->attachQueryToUrl($url, $requestData);
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
        if (!$error) {
            return;
        }

        $errorMessage = "\ncURL transport error: $errorNumber - $error";

        $transportException = new TransportException($errorMessage);

        rewind($verbose);

        $verboseLog = stream_get_contents($verbose);

        if ($verboseLog !== false) {
            $verboseLog = htmlspecialchars($verboseLog);

            $transportException->setData($verboseLog);
        }

        throw $transportException;
    }

    /**
     * Fill timeout options
     *
     * @param $timeout
     * @param $optionName
     * @param $optionNameMs
     *
     * @return array
     */
    private function fillTimeoutOptions($timeout, $optionName, $optionNameMs): array
    {
        $timeoutOptions = [];

        if (is_int($timeout)) {
            $timeoutOptions[$optionName] = $timeout;
        } elseif (is_float($timeout)) {
            $timeoutOptions[$optionNameMs] = $timeout * 1000;
            $timeoutOptions[CURLOPT_NOSIGNAL] = 1;
        }

        return $timeoutOptions;
    }
}
