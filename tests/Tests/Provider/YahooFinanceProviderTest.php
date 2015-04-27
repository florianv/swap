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
use Swap\Provider\YahooFinanceProvider;

class YahooFinanceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported()
    {
        $content = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/unsupported.json');

        $body = $this->getMock('Psr\Http\Message\StreamableInterface');
        $body
            ->expects($this->once())
            ->method('__toString')
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
            ->will($this->returnValue($response));

        $provider = new YahooFinanceProvider($adapter);
        $provider->fetchRate(new CurrencyPair('EUR', 'XXL'));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $content = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/success.json');

        $body = $this->getMock('Psr\Http\Message\StreamableInterface');
        $body
            ->expects($this->once())
            ->method('__toString')
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
            ->with($url)
            ->will($this->returnValue($response));

        $provider = new YahooFinanceProvider($adapter);
        $rate = $provider->fetchRate(new CurrencyPair('EUR', 'USD'));

        $this->assertSame('1.3758', $rate->getValue());
        $this->assertEquals(new \DateTime('2014-05-10 07:23:00'), $rate->getDate());
    }
}
