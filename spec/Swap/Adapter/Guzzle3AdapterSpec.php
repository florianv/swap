<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Swap\Adapter;

use Guzzle\Http\Message\RequestInterface;
use Swap\Exception\AdapterException;
use Guzzle\Http\Message\Response;
use Guzzle\Http\ClientInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Guzzle3AdapterSpec extends ObjectBehavior
{
    function it_is_initializable(ClientInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Adapter\Guzzle3Adapter');
        $this->shouldBeAnInstanceOf('Swap\AdapterInterface');
    }

    function it_gets_a_response_body(
        ClientInterface $client,
        RequestInterface $request,
        Response $response
    )
    {
        $client->get('foo')->willReturn($request);
        $client->send($request)->willReturn($response);
        $response->getBody(true)->willReturn('bar');

        $this->beConstructedWith($client);
        $this->get('foo')->shouldReturn('bar');
    }

    function it_throws_a_client_exception_when_the_request_fails_while_getting_one_response_body(
        ClientInterface $client,
        RequestInterface $request
    )
    {
        $client->get('bar')->willReturn($request);
        $client->send($request)->willThrow(new \Exception('error'));

        $this->beConstructedWith($client);
        $this
            ->shouldThrow(new AdapterException('error'))
            ->duringGet('bar')
        ;
    }

    function it_gets_three_responses_bodies(
        ClientInterface $client,
        RequestInterface $requestOne,
        RequestInterface $requestTwo,
        RequestInterface $requestThree,
        Response $responseOne,
        Response $responseTwo,
        Response $responseThree
    )
    {
        $responseOne->getBody(true)->willReturn('foo');
        $responseTwo->getBody(true)->willReturn('bar');
        $responseThree->getBody(true)->willReturn('baz');

        $client->get('foo')->willReturn($requestOne);
        $client->get('bar')->willReturn($requestTwo);
        $client->get('baz')->willReturn($requestThree);

        $client->send(array($requestOne, $requestTwo, $requestThree))->willReturn(array($responseOne, $responseTwo, $responseThree));

        $this->beConstructedWith($client);
        $this->getAll(array('foo', 'bar', 'baz'))->shouldReturn(array('foo', 'bar', 'baz'));
    }

    function it_throws_a_client_exception_when_the_request_fails_while_getting_three_responses_bodies(
        ClientInterface $client,
        RequestInterface $requestOne,
        RequestInterface $requestTwo,
        RequestInterface $requestThree
    )
    {
        $client->get('foo')->willReturn($requestOne);
        $client->get('bar')->willReturn($requestTwo);
        $client->get('baz')->willReturn($requestThree);

        $client->send(array($requestOne, $requestTwo, $requestThree))->willThrow(new \Exception('oops'));

        $this->beConstructedWith($client);
        $this
            ->shouldThrow(new AdapterException('oops'))
            ->duringGetAll(array('foo', 'bar', 'baz'))
        ;
    }
}
