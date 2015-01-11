<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Provider;

use Swap\Model\CurrencyPair;
use Swap\Provider\OpenExchangeRatesProvider;

class OpenExchangeRatesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_if_base_is_not_usd_and_non_enterprise_mode()
    {
        $provider = new OpenExchangeRatesProvider($this->getMock('Ivory\HttpAdapter\HttpAdapterInterface'), 'secret');
        $provider->fetchRate(new CurrencyPair('EUR', 'EUR'));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_an_exception_with_error_response()
    {
        $uri = 'https://openexchangerates.org/api/latest.json?app_id=secret';
        $content = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/error.json');

        $body = $this->getMock('Psr\Http\Message\StreamableInterface');
        $body
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($content));

        $response = $this->getMock('\Ivory\HttpAdapter\Message\ResponseInterface');
        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($body));

        $adapter = $this->getMock('Ivory\HttpAdapter\HttpAdapterInterface');

        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($response));

        $provider = new OpenExchangeRatesProvider($adapter, 'secret');
        $provider->fetchRate(new CurrencyPair('USD', 'EUR'));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_normal_mode()
    {
        $uri = 'https://openexchangerates.org/api/latest.json?app_id=secret';
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1399748450);
        $content = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success.json');

        $body = $this->getMock('Psr\Http\Message\StreamableInterface');
        $body
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($content));

        $response = $this->getMock('\Ivory\HttpAdapter\Message\ResponseInterface');
        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($body));

        $adapter = $this->getMock('Ivory\HttpAdapter\HttpAdapterInterface');

        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($response));

        $provider = new OpenExchangeRatesProvider($adapter, 'secret');
        $rate = $provider->fetchRate(new CurrencyPair('USD', 'EUR'));

        $this->assertEquals('0.726804', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_enterprise_mode()
    {
        $uri = 'https://openexchangerates.org/api/latest.json?app_id=secret&base=USD&symbols=EUR';
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1399748450);
        $content = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success.json');

        $body = $this->getMock('Psr\Http\Message\StreamableInterface');
        $body
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($content));

        $response = $this->getMock('\Ivory\HttpAdapter\Message\ResponseInterface');
        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($body));

        $adapter = $this->getMock('Ivory\HttpAdapter\HttpAdapterInterface');

        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($response));

        $provider = new OpenExchangeRatesProvider($adapter, 'secret', true);
        $rate = $provider->fetchRate(new CurrencyPair('USD', 'EUR'));

        $this->assertEquals('0.726804', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }
}
