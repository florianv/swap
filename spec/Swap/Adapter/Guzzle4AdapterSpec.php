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

use GuzzleHttp\Message\ResponseInterface;
use Swap\Exception\AdapterException;
use Guzzle\Stream\StreamInterface;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Guzzle4AdapterSpec extends ObjectBehavior
{
    function it_is_initializable(ClientInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Adapter\Guzzle4Adapter');
        $this->shouldBeAnInstanceOf('Swap\AdapterInterface');
    }

    function it_gets_a_response_body(
        ClientInterface $client,
        ResponseInterface $response,
        StreamInterface $responseBody
    )
    {
        $responseBody->__toString()->willReturn('bar');
        $response->getBody()->willReturn($responseBody);
        $client->get('foo')->willReturn($response);

        $this->beConstructedWith($client);
        $this->get('foo')->shouldReturn('bar');
    }

    function it_throws_a_client_exception_when_the_request_fails_while_getting_one_response_body(
        ClientInterface $client
    )
    {
        $client->get('bar')->willThrow(new \Exception('error'));

        $this->beConstructedWith($client);
        $this
            ->shouldThrow(new AdapterException('error'))
            ->duringGet('bar')
        ;
    }
}
