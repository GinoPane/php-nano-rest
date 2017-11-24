<?php 

namespace GinoPane\NanoRest;

use PHPUnit\Framework\TestCase;

/**
 * Corresponding class to test NanoRestTest class
 *
 * @package GinoPane\NanoRest
 * @author Sergey <Gino Pane> Karavay
*/
class NanoRestTest extends TestCase
{
    /**
     * Just check if the NanoRest has no syntax errors
     */
    public function testIfObjectCanBeCreated()
    {
        $object = new NanoRest();

        $this->assertTrue(is_object($object));
    }
}
