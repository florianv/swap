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

use Swap\HistoricalExchangeQuery;
use Swap\Model\CurrencyPair;
use Swap\Provider\GoogleFinanceProvider;
use Swap\ExchangeQuery;

class GoogleFinanceProviderTest extends AbstractProviderTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new GoogleFinanceProvider($this->getMock('Http\Client\HttpClient'));

        $this->assertTrue($provider->support(ExchangeQuery::createFromString('USD/EUR')));
        $this->assertFalse($provider->support(new HistoricalExchangeQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_an_exception_when_rate_not_supported()
    {
        $uri = 'http://www.google.com/finance/converter?a=1&from=EUR&to=XXL';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/GoogleFinance/unsupported.html');

        $provider = new GoogleFinanceProvider($this->getHttpAdapterMock($uri, $content));
        $provider->fetchRate(ExchangeQuery::createFromString('EUR/XXL'));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'http://www.google.com/finance/converter?a=1&from=EUR&to=USD';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/GoogleFinance/success.html');

        $provider = new GoogleFinanceProvider($this->getHttpAdapterMock($url, $content));
        $rate = $provider->fetchRate(ExchangeQuery::createFromString('EUR/USD'));

        $this->assertSame('1.1825', $rate->getValue());
        $this->assertInstanceOf('\DateTime', $rate->getDate());
    }

    /**
     * @test
     */
    public function it_has_no_php_errors()
    {
        $url = 'http://www.google.com/finance/converter?a=1&from=EUR&to=USD';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/GoogleFinance/success.html');

        $provider = new GoogleFinanceProvider($this->getHttpAdapterMock($url, $content));
        $provider->fetchRate(ExchangeQuery::createFromString('EUR/USD'));

        $this->assertNull(error_get_last());
    }
}
