<?php 

namespace GinoPane\NanoRest;

use stdClass;
use PHPUnit\Framework\TestCase;
use GinoPane\NanoRest\Response\JsonResponseContext;
use GinoPane\NanoRest\Response\DummyResponseContext;
use GinoPane\NanoRest\Exceptions\ResponseContextException;

/**
 * Corresponding class to test ResponseContext class
 *
 * @package GinoPane\NanoRest
 * @author Sergey <Gino Pane> Karavay
*/
class ResponseContextTest extends TestCase
{
    public function testIfObjectCanBeCreated()
    {
        $context = new JsonResponseContext();

        $this->assertTrue($context instanceof JsonResponseContext);

        $context = new JsonResponseContext('{"content":100}');

        $this->assertTrue($context instanceof JsonResponseContext);

        $context = new DummyResponseContext('content');

        $this->assertTrue($context instanceof DummyResponseContext);

        $this->assertEquals('content', $context->getContent());
        $this->assertEquals('content', $context->getRaw());
    }

    public function testThatWrongJsonCausesExceptions()
    {
        $this->expectException(ResponseContextException::class);

        new JsonResponseContext('I am invalid JSON');
    }

    /**
     * @dataProvider getHttpCodeStates
     *
     * @param int $code
     * @param bool $hasError
     */
    public function testHttpError(int $code, bool $hasError)
    {
        $context = new JsonResponseContext();

        $context->setHttpStatusCode($code);

        $this->assertEquals($hasError, $context->hasHttpError());
    }

    public function testContentSetAndGet()
    {
        $context = new JsonResponseContext();

        $this->assertEquals($context->getRaw(), $context->getContent());
        $this->assertNull($context->getContent());
        $this->assertEquals([], $context->getArray());
        $this->assertEquals(new stdClass, $context->getObject());

        $context->setContent('{"content":["a","b"]}');

        $this->assertEquals($context->getRaw(), $context->getContent());
        $this->assertEquals('{"content":["a","b"]}', $context->getContent());
        $this->assertEquals(['content' => ['a', 'b']], $context->getArray());
        $this->assertEquals(['a', 'b'], ($context->getObject())->content);

        $context = new DummyResponseContext();

        $this->assertEquals($context->getRaw(), $context->getContent());
        $this->assertNull($context->getContent());
        $this->assertEquals([], $context->getArray());
        $this->assertEquals(new stdClass, $context->getObject());
    }

    public function testStringOutput()
    {
        $context = new JsonResponseContext('{"content":["a","b"]}');

        $expected = '{
    "content": [
        "a",
        "b"
    ]
}';
        $this->assertEquals(str_replace("\r\n", "\n", $expected), (string)$context);

        $context = new DummyResponseContext('content');

        $this->assertEquals('content', (string)$context);
    }

    /**
     * The list of HTTP codes and relevant error state
     *
     * @return array
     */
    public function getHttpCodeStates()
    {
        return [
            [200, false],
            [100, false],
            [301, false],
            [400, true],
            [401, true],
            [403, true],
            [405, true],
            [500, true],
            [502, true]
        ];
    }
}
