<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Adapter;

use Swap\Adapter\Guzzle4Adapter;

class Guzzle4AdapterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('GuzzleHttp\Client')) {
            $this->markTestSkipped('Guzzle4 needs to be installed');
        }
    }

    /**
     * @test
     */
    function it_gets_a_response_body()
    {
        $responseBody = $this->getMock('GuzzleHttp\Stream\StreamInterface');
        $responseBody
            ->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('body'));

        $response = $this->getMock('GuzzleHttp\Message\ResponseInterface');

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($responseBody));

        $client = $this->getMock('GuzzleHttp\ClientInterface');

        $client
            ->expects($this->once())
            ->method('get')
            ->with('uri')
            ->will($this->returnValue($response));

        $adapter = new Guzzle4Adapter($client);
        $this->assertEquals('body', $adapter->get('uri'));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\AdapterException
     */
    function it_throws_an_exception_when_the_request_fails_while_getting_one_response_body()
    {
        $client = $this->getMock('GuzzleHttp\ClientInterface');

        $client
            ->expects($this->once())
            ->method('get')
            ->with('uri')
            ->will($this->throwException(new \Exception()));

        $adapter = new Guzzle4Adapter($client);
        $adapter->get('uri');
    }
}
