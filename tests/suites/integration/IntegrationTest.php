<?php 

namespace GinoPane\NanoRest;

use PHPUnit\Framework\TestCase;
use GinoPane\NanoRest\Request\RequestContext;
use GinoPane\NanoRest\Response\ResponseContext;

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

        $requestContext = new RequestContext(['uri' => 'http://httpbin.org/get']);

        /** @var ResponseContext $responseContext */
        $responseContext = $nanoRest->sendRequest($requestContext);

        var_dump($responseContext);
    }
}
