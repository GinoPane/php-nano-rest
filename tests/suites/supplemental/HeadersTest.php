<?php 

namespace GinoPane\NanoRest;

use PHPUnit\Framework\TestCase;

use GinoPane\NanoRest\Supplemental\Headers;

/**
 * Test simple headers container
 *
 * @package GinoPane\NanoRest
 * @author Sergey <Gino Pane> Karavay
*/
class HeadersTest extends TestCase
{
    public function testIfObjectCanBeCreated()
    {
        $headers = new Headers();

        $this->assertTrue(is_object($headers));
    }

    public function testIfObjectCanBeCreatedWithParameters()
    {
        $headers = new Headers(['foo' => 'bar']);

        $this->assertTrue(is_object($headers));

        $storedHeaders = $headers->getHeaders();

        $this->assertArrayHasKey('foo', $storedHeaders);
        $this->assertTrue($headers->headerExists('foo'));
        $this->assertEquals('bar', $headers->getHeader('foo'));
    }

    /**
     * @dataProvider getDataForHeadersParsing
     *
     * @param string $headersToParse
     * @param array $expectedHeaders
     */
    public function testParseHeaders(string $headersToParse, array $expectedHeaders)
    {
        $parsedHeaders = Headers::parseHeaders($headersToParse);

        $this->assertEquals($expectedHeaders, $parsedHeaders);
    }

    public function testIfHeadersCanBeSetFromString()
    {
        $headers = new Headers();

        $headerString = "
            Cache-Control: no-cache
            Cache-Control: no-store
            Server: meinheld/0.6.1
        ";

        $headers->setHeadersFromString($headerString);

        $this->assertTrue($headers->headerExists('Cache-Control'));
        $this->assertEquals("no-cache, no-store", $headers->getHeader('Cache-Control'));

        $this->assertFalse($headers->headerExists('Foo'));
        $this->assertNull($headers->getHeader('Foo'));
    }

    public function testIfHeadersCanBeCreatedFromString()
    {
        $headerString = "
            Cache-Control: no-cache
            Cache-Control: no-store
            Server: meinheld/0.6.1
        ";

        $headers = Headers::createFromString($headerString);

        $this->assertTrue($headers->headerExists('Cache-Control'));
        $this->assertEquals("no-cache, no-store", $headers->getHeader('Cache-Control'));

        $this->assertFalse($headers->headerExists('Foo'));
        $this->assertNull($headers->getHeader('Foo'));
    }

    public function testIfHeadersForRequestAreAvailable()
    {
        $headers = new Headers(['foo' => 'bar']);

        $this->assertTrue(is_object($headers));

        $headersForRequest = $headers->getHeadersForRequest();

        $this->assertTrue($headers->headerExists('foo'));
        $this->assertArrayHasKey('foo', $headersForRequest);

        $this->assertEquals('bar', $headers->getHeader('foo'));
        $this->assertEquals('foo: bar', $headersForRequest['foo']);
    }

    public function getDataForHeadersParsing()
    {
        return [
            [
                "HTTP/1.1 200 OK
                    Connection: keep-alive
                    Server: meinheld/0.6.1
                    Date: Fri, 24 Nov 2017 09:26:03 GMT
                    Content-Type: application/json
                    Access-Control-Allow-Origin: *
                    Access-Control-Allow-Credentials: true
                    X-Powered-By: Flask
                    Server: foo
                    X-Processed-Time: 0.000829935073853
                    Server: bar
                    Content-Length: 181
                    Via: 1.1 vegur",
                [
                    'Connection' => 'keep-alive',
                    'Server' => 'meinheld/0.6.1, foo, bar',
                    'Date' => 'Fri, 24 Nov 2017 09:26:03 GMT',
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Credentials' => 'true',
                    'X-Powered-By' => 'Flask',
                    'X-Processed-Time' => '0.000829935073853',
                    'Content-Length' => '181',
                    'Via' => '1.1 vegur'
                ]
            ],
            [
                'HTTP/1.1 200 OK',
                []
            ]
        ];
    }
}


