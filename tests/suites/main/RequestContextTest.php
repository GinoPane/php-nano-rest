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
    /**
     * Just check if the NanoRest has no syntax errors
     */
    public function testIfObjectCanBeCreated()
    {
        $context = new RequestContext();

        $this->assertTrue($context instanceof RequestContext);

        $context = new RequestContext('uri');

        $this->assertTrue($context instanceof RequestContext);

        $this->assertEquals('uri', $context->getUri());
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
