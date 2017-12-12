<?php 

namespace GinoPane\NanoRest;

use PHPUnit\Framework\TestCase;
use GinoPane\NanoRest\Request\RequestContext;
use GinoPane\NanoRest\Response\ResponseContext;
use GinoPane\NanoRest\Exceptions\TransportException;

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

        $nanoRest->setResponseContext(ResponseContext::getByType(ResponseContext::RESPONSE_TYPE_JSON));

        $requestContext = (new RequestContext('https://httpbin.org/get'))
            ->setRequestParameters([
                'foo' => 'bar'
            ])
            ->setHeaders([
                'bar' => 'baz'
            ]);

        $responseContext = $nanoRest->sendRequest($requestContext);

        $this->assertEquals(200, $responseContext->getHttpStatusCode());
        $this->assertFalse($responseContext->hasHttpError());

        $this->assertTrue($responseContext->getRequestContext() instanceof RequestContext);
        $this->assertEquals('https://httpbin.org/get', $responseContext->getRequestContext()->getUrl());

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

        $nanoRest->setResponseContext(ResponseContext::getByType(ResponseContext::RESPONSE_TYPE_JSON));

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

        $nanoRest->setResponseContext(ResponseContext::getByType(ResponseContext::RESPONSE_TYPE_JSON));

        $requestContext = (new RequestContext('http://httpbin.org/post'))
            ->setMethod(RequestContext::METHOD_POST)
            ->setRequestParameters([
                'foo' => 'bar'
            ])->setHeaders([
                'bar' => 'baz'
            ])->setData([
                'password' => 'secret'
            ])->setContentType(RequestContext::CONTENT_TYPE_FORM_URLENCODED);

        $responseContext = $nanoRest->sendRequest($requestContext);

        $this->assertEquals(200, $responseContext->getHttpStatusCode());
        $this->assertEquals('OK', $responseContext->getHttpStatusMessage());
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

    /**
     * Test that request will fail for invalid endpoint
     */
    public function testFailingRequest()
    {
        $this->expectException(TransportException::class);

        $nanoRest = new NanoRest();

        $requestContext = (new RequestContext("http://idont.exist"))
            ->setMethod(RequestContext::METHOD_PUT)
            ->setConnectionTimeout(1)
            ->setTimeout(3);

        $nanoRest->sendRequest($requestContext);
    }

    /**
     * Test that request will fail for invalid endpoint
     */
    public function testFailingRequestVerboseOutput()
    {
        $nanoRest = new NanoRest();

        $requestContext = (new RequestContext("http://idont.exist"))
            ->setMethod(RequestContext::METHOD_PUT)
            ->setConnectionTimeout(0.5)
            ->setTimeout(1.5);

        try {
            $nanoRest->sendRequest($requestContext);

            $this->fail('Exception was not thrown!');
        } catch (TransportException $exception) {
            $this->assertNotEmpty($exception->getData());
        }
    }
}
