<?php

namespace GinoPane\NanoRest;

define('ROOT_DIRECTORY', dirname(dirname(__FILE__)));

use GinoPane\NanoRest\{
    Request\RequestContext,
    Supplemental\CurlHelper,
    Response\ResponseContext
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
     * CurlHelper for handling curl-specific logic
     *
     * @var CurlHelper
     */
    private $curlHelper = null;

    /**
     * Default response context
     *
     * @var ResponseContext
     */
    private $responseContext = null;

    /**
     * NanoRest constructor
     */
    public function __construct()
    {
        $this->curlHelper = new CurlHelper();
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
    public function sendRequest(
        RequestContext $requestContext,
        ResponseContext $responseContext = null
    ): ResponseContext {
        $curlHandle = $this->curlHelper->getRequestHandle($requestContext);

        if (!$responseContext) {
            $responseContext = $this->getResponseContext();
        }

        list(
            'httpStatus'    => $status,
            'response'      => $content,
            'headers'       => $headers
        ) = $this->curlHelper->executeRequestHandle($curlHandle);

        $responseContext->setRequestContext($requestContext)
            ->setHttpStatusCode($status)
            ->setContent($content);

        $responseContext->headers()->setHeadersFromString($headers);

        return $responseContext;
    }
}
