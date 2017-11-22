<?php 

namespace GinoPane\NanoRest;

use PHPUnit\Framework\TestCase;

/**
*  Corresponding class to test YourClass class
*
*  For each class in your library, there should be a corresponding unit test
*  Unit-Tests should be as much as possible independent from other test going on.
*
*  @author Sergey <Gino Pane> Karavay
*/
class NanoRestTest extends TestCase
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