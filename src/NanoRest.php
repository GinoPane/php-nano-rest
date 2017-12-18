<?php

namespace GinoPane\NanoRest;

define('ROOT_DIRECTORY', dirname(__FILE__, 2));

use GinoPane\NanoRest\{
    Request\RequestContext,
    Supplemental\CurlHelper,
    Response\ResponseContext,
    Exceptions\TransportException,
    Exceptions\ResponseContextException
};

/**
 * Class NanoRest
 *
 * Abstract implementation of transport layer
 *
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
     * NanoRest constructor
     */
    public function __construct()
    {
        $this->curlHelper = new CurlHelper();
    }

    /**
     * Send previously prepared request
     *
     * @param RequestContext $requestContext
     *
     * @throws TransportException
     * @throws ResponseContextException
     *
     * @return ResponseContext
     */
    public function sendRequest(
        RequestContext $requestContext
    ): ResponseContext {
        $curlHandle = $this->curlHelper->getRequestHandle($requestContext);

        $responseContext = $requestContext->getResponseContextObject();

        list(
            'httpStatus'    => $status,
            'response'      => $content,
            'headers'       => $headers
        ) = $this->curlHelper->executeRequestHandle($curlHandle);

        $responseContext->setRequestContext($requestContext)
            ->setHttpStatusCode((int)$status)
            ->setContent($content);

        $responseContext->headers()->setHeadersFromString($headers);

        return $responseContext;
    }
}
