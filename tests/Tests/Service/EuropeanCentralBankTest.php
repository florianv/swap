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

use Swap\ExchangeRateQuery;
use Swap\HistoricalExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\Service\EuropeanCentralBank;

class EuropeanCentralBankTest extends ServiceTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new EuropeanCentralBank($this->getMock('Http\Client\HttpClient'));
        $this->assertFalse($provider->support(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'))));
        $this->assertTrue($provider->support(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     * @expectedExceptionMessage The currency pair "EUR/XXL" is not supported by the service "Swap\Service\EuropeanCentralBank".
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/EuropeanCentralBank/success.xml');

        $provider = new EuropeanCentralBank($this->getHttpAdapterMock($url, $content));
        $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/XXL')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/EuropeanCentralBank/success.xml');

        $provider = new EuropeanCentralBank($this->getHttpAdapterMock($url, $content));
        $rate = $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/BGN')));

        $this->assertSame('1.9558', $rate->getValue());
        $this->assertEquals(new \DateTime('2015-01-07'), $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/EuropeanCentralBank/historical.xml');

        $provider = new EuropeanCentralBank($this->getHttpAdapterMock($url, $content));
        $rate = $provider->get(
            new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/JPY'), new \DateTime('2016-08-23'))
        );

        $this->assertSame('113.48', $rate->getValue());
        $this->assertEquals(new \DateTime('2016-08-23'), $rate->getDate());
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedDateException
     * @expectedExceptionMessage The date "2015-08-23" is not supported by the service "Swap\Service\EuropeanCentralBank".
     */
    public function it_throws_an_exception_when_historical_date_is_not_supported()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/EuropeanCentralBank/historical.xml');

        $provider = new EuropeanCentralBank($this->getHttpAdapterMock($url, $content));
        $provider->get(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/JPY'), new \DateTime('2015-08-23')));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     * @expectedExceptionMessage The currency pair "EUR/XXL" is not supported by the service "Swap\Service\EuropeanCentralBank".
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported_historical()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/EuropeanCentralBank/historical.xml');

        $provider = new EuropeanCentralBank($this->getHttpAdapterMock($url, $content));
        $provider->get(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/XXL'), new \DateTime('2016-08-23')));
    }
}
