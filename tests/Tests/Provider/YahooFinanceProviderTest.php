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
use Swap\Provider\YahooFinanceProvider;
use Swap\ExchangeQuery;

class YahooFinanceProviderTest extends AbstractProviderTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new YahooFinanceProvider($this->getMock('Http\Client\HttpClient'));

        $this->assertTrue($provider->support(ExchangeQuery::createFromString('USD/EUR')));
        $this->assertFalse($provider->support(new HistoricalExchangeQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported()
    {
        $url = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURXXL%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/YahooFinance/unsupported.json');

        $provider = new YahooFinanceProvider($this->getHttpAdapterMock($url, $content));
        $provider->fetchRate(ExchangeQuery::createFromString('EUR/XXL'));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/YahooFinance/success.json');

        $provider = new YahooFinanceProvider($this->getHttpAdapterMock($url, $content));
        $rate = $provider->fetchRate(ExchangeQuery::createFromString('EUR/USD'));

        $this->assertSame('1.3758', $rate->getValue());
        $this->assertEquals(new \DateTime('2014-05-10 07:23:00'), $rate->getDate());
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_the_error_as_exception()
    {
        $url = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/YahooFinance/error.json');

        $provider = new YahooFinanceProvider($this->getHttpAdapterMock($url, $content));
        $provider->fetchRate(ExchangeQuery::createFromString('EUR/USD'));
    }
}
