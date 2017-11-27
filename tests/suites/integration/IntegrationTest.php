<?php 

namespace GinoPane\NanoRest;

use GinoPane\NanoRest\Response\JsonResponseContext;
use PHPUnit\Framework\TestCase;
use GinoPane\NanoRest\Request\RequestContext;
use GinoPane\NanoRest\Response\ResponseContext;

/**
 * Integration tests for NanoRest using http://httpbin.org
 *
 * @package GinoPane\NanoRest
 * @author Sergey <Gino Pane> Karavay
*/
class IntegrationTest extends TestCase
{
    /**
     * Test get request using http://httpbin.org/get endpoint
     */
    public function testGetRequest()
    {
        $nanoRest = new NanoRest();

        $nanoRest->setResponseContext(new JsonResponseContext());

        $requestContext = (new RequestContext('http://httpbin.org/get'))
            ->setRequestParameters([
                'foo' => 'bar'
            ])
            ->setHeaders([
                'bar' => 'baz'
            ]);

        /** @var ResponseContext $responseContext */
        $responseContext = $nanoRest->sendRequest($requestContext);

        $this->assertEquals(200, $responseContext->getHttpStatusCode());
        $this->assertFalse($responseContext->hasHttpError());

        $responseArray = $responseContext->getArray();

        $this->assertNotEmpty($responseArray['args']);
        $this->assertArrayHasKey('foo', $responseArray['args']);
        $this->assertEquals('bar', $responseArray['args']['foo']);

        $this->assertNotEmpty($responseArray['headers']);
        $this->assertArrayHasKey('Bar', $responseArray['headers']);
        $this->assertEquals('baz', $responseArray['headers']['Bar']);
    }

    /**
     * Test get request using http://httpbin.org/post endpoint
     * by sending data as plain text
     */
    public function testPostRequestWithPlainTextContent()
    {
        $nanoRest = new NanoRest();

        $nanoRest->setResponseContext(new JsonResponseContext());

        $requestContext = (new RequestContext('http://httpbin.org/post'))
            ->setMethod(RequestContext::METHOD_POST)
            ->setRequestParameters([
                'foo' => 'bar'
            ])
            ->setData('Hello world!')
            ->setContentType(RequestContext::CONTENT_TYPE_TEXT_PLAIN) //being set by default
            ->setHeaders([
                'bar' => 'baz'
            ]);

        /** @var ResponseContext $responseContext */
        $responseContext = $nanoRest->sendRequest($requestContext);

        $this->assertEquals(200, $responseContext->getHttpStatusCode());
        $this->assertFalse($responseContext->hasHttpError());

        $responseArray = $responseContext->getArray();

        $this->assertNotEmpty($responseArray['args']);
        $this->assertArrayHasKey('foo', $responseArray['args']);
        $this->assertEquals('bar', $responseArray['args']['foo']);

        $this->assertNotEmpty($responseArray['headers']);
        $this->assertArrayHasKey('Bar', $responseArray['headers']);
        $this->assertEquals('baz', $responseArray['headers']['Bar']);

        $this->assertNotEmpty($responseArray['data']);
        $this->assertEquals('Hello world!', $responseArray['data']);
    }

    /**
     * Test get request using http://httpbin.org/post endpoint
     * by sending data as form data
     */
    public function testPostRequestWithFormDataContent()
    {
        $nanoRest = new NanoRest();

        $nanoRest->setResponseContext(new JsonResponseContext());

        $requestContext = (new RequestContext('http://httpbin.org/post'))
            ->setMethod(RequestContext::METHOD_POST)
            ->setRequestParameters([
                'foo' => 'bar'
            ])->setHeaders([
                'bar' => 'baz'
            ])->setData([
                'password' => 'secret'
            ])->setContentType(RequestContext::CONTENT_TYPE_FORM_URLENCODED);

        /** @var ResponseContext $responseContext */
        $responseContext = $nanoRest->sendRequest($requestContext);

        $this->assertEquals(200, $responseContext->getHttpStatusCode());
        $this->assertFalse($responseContext->hasHttpError());

        $responseArray = $responseContext->getArray();

        $this->assertNotEmpty($responseArray['args']);
        $this->assertArrayHasKey('foo', $responseArray['args']);
        $this->assertEquals('bar', $responseArray['args']['foo']);

        $this->assertNotEmpty($responseArray['headers']);
        $this->assertArrayHasKey('Bar', $responseArray['headers']);
        $this->assertEquals('baz', $responseArray['headers']['Bar']);

        $this->assertNotEmpty($responseArray['form']['password']);
        $this->assertEquals('secret', $responseArray['form']['password']);
    }
}
