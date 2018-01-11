<?php

namespace GinoPane\NanoRest;

define(__NAMESPACE__ . '\ROOT_DIRECTORY', dirname(__FILE__, 2));

use GinoPane\NanoRest\{
    Request\RequestContext,
    Supplemental\CurlHelper,
    Response\ResponseContextAbstract,
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
     * @return ResponseContextAbstract
     */
    public function sendRequest(
        RequestContext $requestContext
    ): ResponseContextAbstract {
        $curlHandle = $this->curlHelper->getRequestHandle($requestContext);

        $responseContext = $requestContext->getResponseContextObject();

        list(
            'httpStatus'    => $status,
            'response'      => $content,
            'headers'       => $headers
        ) = $this->curlHelper->executeRequestHandle($curlHandle);

        $responseContext->setRequestContext($requestContext)
            ->setHttpStatusCode((int)$status);

        if ($content) {
            $responseContext->setContent($content);
        }

        $responseContext->headers()->setHeadersFromString($headers);

        return $responseContext;
    }
}
