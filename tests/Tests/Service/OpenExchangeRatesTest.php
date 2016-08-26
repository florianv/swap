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
use Swap\Service\OpenExchangeRates;

class OpenExchangeRatesTest extends ServiceTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "app_id" option must be provided.
     */
    public function it_throws_an_exception_if_app_id_option_missing()
    {
        new OpenExchangeRates($this->getMock('Http\Client\HttpClient'));
    }

    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new OpenExchangeRates($this->getMock('Http\Client\HttpClient'), null, ['app_id' => 'secret']);

        $this->assertFalse($provider->support(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/EUR'))));
        $this->assertTrue($provider->support(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_an_exception_with_error_response()
    {
        $uri = 'https://openexchangerates.org/api/latest.json?app_id=secret';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/OpenExchangeRates/error.json');

        $provider = new OpenExchangeRates($this->getHttpAdapterMock($uri, $content), null, ['app_id' => 'secret']);
        $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_normal_mode()
    {
        $uri = 'https://openexchangerates.org/api/latest.json?app_id=secret';
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1399748450);
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/OpenExchangeRates/success.json');

        $provider = new OpenExchangeRates($this->getHttpAdapterMock($uri, $content), null, ['app_id' => 'secret']);
        $rate = $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));

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
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/OpenExchangeRates/success.json');

        $provider = new OpenExchangeRates($this->getHttpAdapterMock($uri, $content), null, ['app_id' => 'secret', 'enterprise' => true]);
        $rate = $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));

        $this->assertEquals('0.726804', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate()
    {
        $url = 'https://openexchangerates.org/api/historical/2016-08-23.json?app_id=secret';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/OpenExchangeRates/historical_success.json');

        $provider = new OpenExchangeRates($this->getHttpAdapterMock($url, $content), null, ['app_id' => 'secret']);
        $rate = $provider->get(
            new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/AED'), new \DateTime('2016-08-23'))
        );

        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(982342800);

        $this->assertEquals('3.67246', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate_enterprise()
    {
        $url = 'https://openexchangerates.org/api/historical/2016-08-23.json?app_id=secret&base=USD&symbols=EUR';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/OpenExchangeRates/historical_success.json');

        $provider = new OpenExchangeRates($this->getHttpAdapterMock($url, $content), null, ['app_id' => 'secret', 'enterprise' => true]);
        $rate = $provider->get(
            new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'), new \DateTime('2016-08-23'))
        );

        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(982342800);

        $this->assertEquals('1.092882', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     * @expectedExceptionMessage Historical rates for the requested date are not available - please try a different date, or contact support@openexchangerates.org.
     */
    public function it_throws_an_exception_when_historical_date_is_not_supported()
    {
        $url = 'https://openexchangerates.org/api/historical/1900-08-23.json?app_id=secret';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/OpenExchangeRates/historical_error.json');

        $provider = new OpenExchangeRates($this->getHttpAdapterMock($url, $content), null, ['app_id' => 'secret']);

        $provider->get(
            new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/AED'), new \DateTime('1900-08-23'))
        );
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     * @expectedExceptionMessage The currency pair "USD/XXL" is not supported by the provider.
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported_historical()
    {
        $url = 'https://openexchangerates.org/api/historical/2016-08-23.json?app_id=secret';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/OpenExchangeRates/historical_success.json');

        $provider = new OpenExchangeRates($this->getHttpAdapterMock($url, $content), null, ['app_id' => 'secret']);

        $provider->get(
            new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/XXL'), new \DateTime('2016-08-23'))
        );
    }
}
