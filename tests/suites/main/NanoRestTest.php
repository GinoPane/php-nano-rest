<?php 

namespace GinoPane\NanoRest;

use PHPUnit\Framework\TestCase;

/**
*  Corresponding class to test YourClass class
*
*  For each class in your library, there should be a corresponding unit test
*
 * @package GinoPane\NanoRest
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
