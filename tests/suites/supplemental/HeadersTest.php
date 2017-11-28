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

    public function testIfHeadersCanBeSetAndMerged()
    {
        $headers = new Headers(['foo' => 'bar']);

        $storedHeaders = $headers->getHeaders();

        $this->assertArrayHasKey('foo', $storedHeaders);
        $this->assertTrue($headers->headerExists('foo'));
        $this->assertEquals('bar', $headers->getHeader('foo'));

        $headers->setHeaders(['bar' => 'baz']);

        $storedHeaders = $headers->getHeaders();

        $this->assertArrayNotHasKey('foo', $storedHeaders);
        $this->assertArrayHasKey('bar', $storedHeaders);
        $this->assertTrue($headers->headerExists('bar'));
        $this->assertEquals('baz', $headers->getHeader('bar'));

        $headers->mergeHeaders(['foo' => 'bar']);

        $storedHeaders = $headers->getHeaders();

        $this->assertArrayHasKey('foo', $storedHeaders);
        $this->assertArrayHasKey('bar', $storedHeaders);
        $this->assertTrue($headers->headerExists('foo'));
        $this->assertTrue($headers->headerExists('bar'));
        $this->assertEquals('bar', $headers->getHeader('foo'));
        $this->assertEquals('baz', $headers->getHeader('bar'));
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

        $this->assertJsonStringEqualsJsonString(json_encode($expectedHeaders), json_encode($parsedHeaders));
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
                    'connection' => 'keep-alive',
                    'server' => 'meinheld/0.6.1, foo, bar',
                    'date' => 'Fri, 24 Nov 2017 09:26:03 GMT',
                    'x-powered-by' => 'Flask',
                    'x-processed-time' => '0.000829935073853',
                    'content-length' => '181',
                    'via' => '1.1 vegur',
                    'content-type' => 'application/json',
                    'access-control-allow-origin' => '*',
                    'access-control-allow-credentials' => 'true',
                ]
            ],
            [
                'HTTP/1.1 200 OK',
                []
            ]
        ];
    }
}


