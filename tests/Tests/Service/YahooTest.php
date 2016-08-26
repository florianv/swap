<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Service;

use Swap\HistoricalExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\ExchangeRateQuery;
use Swap\Service\Yahoo;

class YahooTest extends ServiceTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new Yahoo($this->getMock('Http\Client\HttpClient'));

        $this->assertTrue($provider->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'))));
        $this->assertFalse($provider->supportQuery(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported()
    {
        $url = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURXXL%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/YahooFinance/unsupported.json');

        $provider = new Yahoo($this->getHttpAdapterMock($url, $content));
        $provider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/XXL')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/YahooFinance/success.json');

        $provider = new Yahoo($this->getHttpAdapterMock($url, $content));
        $rate = $provider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));

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

        $provider = new Yahoo($this->getHttpAdapterMock($url, $content));
        $provider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));
    }
}
