<?php 

namespace GinoPane\NanoRest;

use PHPUnit\Framework\TestCase;

/**
 * Test simple headers container
 *
 * @package GinoPane\NanoRest
 * @author Sergey <Gino Pane> Karavay
*/
class HeadersTest extends TestCase
{
    /**
     * Just check if the NanoRest has no syntax errors
     */
    public function testIsThereAnySyntaxError()
    {
        $object = new NanoRest();

        $this->assertTrue(is_object($object));
    }
}
