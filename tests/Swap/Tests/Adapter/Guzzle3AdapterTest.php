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

use Swap\Adapter\Guzzle3Adapter;

class Guzzle3AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_gets_a_response_body()
    {
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');

        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $response
            ->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('body'));

        $client = $this->getMock('Guzzle\Http\ClientInterface');

        $client
            ->expects($this->once())
            ->method('get')
            ->with('uri')
            ->will($this->returnValue($request));

        $client
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->returnValue($response));

        $adapter = new Guzzle3Adapter($client);
        $this->assertEquals('body', $adapter->get('uri'));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\AdapterException
     */
    function it_throws_an_exception_when_the_request_fails_while_getting_one_response_body()
    {
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $client = $this->getMock('Guzzle\Http\ClientInterface');

        $client
            ->expects($this->once())
            ->method('get')
            ->with('uri')
            ->will($this->returnValue($request));

        $client
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->throwException(new \Exception()));

        $adapter = new Guzzle3Adapter($client);
        $adapter->get('uri');
    }

    /**
     * @test
     */
    function it_gets_three_responses_bodies()
    {
        $responseOne = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $responseOne
            ->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('foo'));

        $responseTwo = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $responseTwo
            ->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('bar'));

        $responseThree = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $responseThree
            ->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('baz'));

        $requestOne = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $requestTwo = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $requestThree = $this->getMock('Guzzle\Http\Message\RequestInterface');

        $client = $this->getMock('Guzzle\Http\ClientInterface');

        $client
            ->expects($this->at(0))
            ->method('get')
            ->with('foo')
            ->will($this->returnValue($requestOne));

        $client
            ->expects($this->at(1))
            ->method('get')
            ->with('bar')
            ->will($this->returnValue($requestTwo));

        $client
            ->expects($this->at(2))
            ->method('get')
            ->with('baz')
            ->will($this->returnValue($requestThree));

        $client
            ->expects($this->once())
            ->method('send')
            ->with(array($requestOne, $requestTwo, $requestThree))
            ->will($this->returnValue(array($responseOne, $responseTwo, $responseThree)));

        $adapter = new Guzzle3Adapter($client);
        $this->assertEquals(array('foo', 'bar', 'baz'), $adapter->getAll(array('foo', 'bar', 'baz')));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\AdapterException
     */
    function it_throws_an_exception_when_the_request_fails_while_getting_three_responses_bodies()
    {
        $requestOne = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $requestTwo = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $requestThree = $this->getMock('Guzzle\Http\Message\RequestInterface');

        $client = $this->getMock('Guzzle\Http\ClientInterface');

        $client
            ->expects($this->at(0))
            ->method('get')
            ->with('foo')
            ->will($this->returnValue($requestOne));

        $client
            ->expects($this->at(1))
            ->method('get')
            ->with('bar')
            ->will($this->returnValue($requestTwo));

        $client
            ->expects($this->at(2))
            ->method('get')
            ->with('baz')
            ->will($this->returnValue($requestThree));

        $client
            ->expects($this->once())
            ->method('send')
            ->with(array($requestOne, $requestTwo, $requestThree))
            ->will($this->throwException(new \Exception()));

        $adapter = new Guzzle3Adapter($client);
        $adapter->getAll(array('foo', 'bar', 'baz'));
    }
}
