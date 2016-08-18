<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Provider;

use Swap\Http\IvoryAdapter;

abstract class AbstractProviderTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Create a mocked Http adapter.
     *
     * @param string $url     The url
     * @param string $content The body content
     *
     * @return IvoryAdapter
     */
    protected function getHttpAdapterMock($url, $content)
    {
        $body = $this->getMock('Psr\Http\Message\StreamInterface');
        $body
            ->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue($content));

        $response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($body));

        $adapter = $this->getMock(IvoryAdapter::class);

        $adapter
            ->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function ($arg) use ($url) {
                return $arg->getUri()->__toString() === $url;
            }))
            ->will($this->returnValue($response));

        return $adapter;
    }
}
