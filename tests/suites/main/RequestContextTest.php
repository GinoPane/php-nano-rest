<?php 

namespace GinoPane\NanoRest;

use PHPUnit\Framework\TestCase;
use GinoPane\NanoRest\Request\RequestContext;
use GinoPane\NanoRest\Exceptions\RequestContextException;

/**
 * Corresponding class to test RequestContext class
 *
 * @package GinoPane\NanoRest
 * @author Sergey <Gino Pane> Karavay
*/
class RequestContextTest extends TestCase
{
    public function testIfObjectCanBeCreated()
    {
        $context = new RequestContext();

        $this->assertTrue($context instanceof RequestContext);

        $context = new RequestContext('http://some.url');

        $this->assertTrue($context instanceof RequestContext);

        $this->assertEquals('http://some.url', $context->getUri());
    }

    public function testThatWrongUriCausesExceptions()
    {
        $this->expectException(RequestContextException::class);

        $context = new RequestContext();

        $context->setUri('i am wrong');
    }

    public function testThatProxyCanBeSet()
    {
        $context = new RequestContext();

        $context->setProxy('http://some.url');

        $this->assertEquals('http://some.url', $context->getProxy());
    }

    public function testThatWrongProxyCausesExceptions()
    {
        $this->expectException(RequestContextException::class);

        $context = new RequestContext();

        $context->setProxy('i am wrong');
    }

    /**
     * @dataProvider getValidCurlOptions
     *
     * @param int $option
     * @param $value
     */
    public function testThatCurlOptionsCanBeSet(int $option, $value)
    {
        $options = (new RequestContext())->setCurlOption($option, $value)->getCurlOptions();

        $this->assertArrayHasKey($option, $options);
        $this->assertEquals($options[$option], $value);
    }

    public function testThatCurlOptionsCanBeSetInBulk()
    {
        $context = new RequestContext();

        $context->setCurlOption(CURLOPT_TIMEOUT, 100);
        $context->setCurlOption(CURLOPT_URL, 'some_url');

        $options = [
            CURLOPT_TIMEOUT => 200,
            CURLOPT_CONNECTTIMEOUT => 300
        ];

        $context->setCurlOptions($options);

        $this->assertEquals($options, $context->getCurlOptions());
    }

    /**
     * @dataProvider getInvalidCurlOptions
     *
     * @param int $option
     */
    public function testThatCurlOptionsThrowExceptions(int $option)
    {
        $this->expectException(RequestContextException::class);

        (new RequestContext())->setCurlOption($option, 1);
    }

    public function testThatHeadersCanBeSet()
    {
        $request = new RequestContext();

        $request->setHeaders(['header' => 'value']);

        $this->assertEquals(['header' => 'value'], $request->headers()->getHeaders());

        $request->headers()->mergeHeaders(['header' => 'new_value']);

        $this->assertEquals(['header' => 'new_value'], $request->headers()->getHeaders());
    }

    /**
     * @dataProvider getValidMethodOptions
     *
     * @param string $method
     * @param string $expected
     */
    public function testThatMethodCanBeSet(string $method, string $expected)
    {
        $request = new RequestContext();

        $request->setMethod($method);

        $this->assertEquals($expected, $request->getMethod());
    }

    public function testThatWrongMethodCausesException()
    {
        $this->expectException(RequestContextException::class);

        $request = new RequestContext();

        $request->setMethod("I don't exist");
    }

    public function testThatCharsetCanBeSet()
    {
        $request = new RequestContext();

        $request->setCharset(RequestContext::CHARSET_ISO88591);

        $this->assertEquals(RequestContext::CHARSET_ISO88591, $request->getCharset());
    }

    public function testIfRequestHeadersAreValid()
    {
        $request = new RequestContext();

        $request->headers()->setHeaders(['foo' => 'bar']);

        $this->assertEquals([
            'foo' => 'foo: bar',
            'content-type' => 'content-type: text/plain; charset=UTF-8'
        ], $request->getRequestHeaders());

        $request->setContentType(RequestContext::CONTENT_TYPE_TEXT_PLAIN . "; charset=my_charset");

        $this->assertEquals([
            'foo' => 'foo: bar',
            'content-type' => 'content-type: text/plain; charset=my_charset'
        ], $request->getRequestHeaders());
    }

    public function testThatStringOutputWorksAsExpected()
    {
        $context = new RequestContext();

        $context->setUri('http://example.com');
        $context->headers()->setHeaders(['foo' => 'bar']);
        $context->setData(['a' => '1', 'b' => 'c']);

        $expected = <<<EXPECTED
===================
Method: GET
Request URI: http://example.com
===================
Headers:

Array
(
    [foo] => foo: bar
    [content-type] => content-type: text/plain; charset=UTF-8
)

===================
Data:

Array
(
    [a] => 1
    [b] => c
)

===================
Request Parameters:

No request parameters were set
===================
EXPECTED;

        $this->assertEquals($expected, (string)$context);

        $context->setUri('http://example.com');
        $context->headers()->setHeaders([]);
        $context->setData([]);
        $context->setRequestParameters(['a' => '1', 'b' => 'c']);
        $context->setContentType('');

        $expected = <<<EXPECTED
===================
Method: GET
Request URI: http://example.com?a=1&b=c
===================
Headers:

No headers were set
===================
Data:

No data was set
===================
Request Parameters:

Array
(
    [a] => 1
    [b] => c
)

===================
EXPECTED;

        $this->assertEquals($expected, (string)$context);
    }

    /**
     * @return array
     */
    public function getValidMethodOptions()
    {
        return [
            [RequestContext::METHOD_CONNECT, RequestContext::METHOD_CONNECT],
            [RequestContext::METHOD_POST, RequestContext::METHOD_POST],
            [RequestContext::METHOD_GET, RequestContext::METHOD_GET],
            ['gET', RequestContext::METHOD_GET],
            ['Post', RequestContext::METHOD_POST]
        ];
    }

    /**
     * @return array
     */
    public function getValidCurlOptions()
    {
        return [
            [CURLOPT_CRLF, true],
            [CURLOPT_FOLLOWLOCATION, false],
            [CURLOPT_NOBODY, true],
            [CURLOPT_CONNECTTIMEOUT, 100.40],
            [CURLOPT_TIMEOUT_MS, 3000],
            [CURLOPT_COOKIEJAR, 'file'],
            [CURLOPT_REFERER, 'referer']
        ];
    }

    /**
     * @return array
     */
    public function getInvalidCurlOptions()
    {
        return [
            [0],
            [-100]
        ];
    }
}
