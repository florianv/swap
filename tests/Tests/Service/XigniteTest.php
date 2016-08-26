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

use Swap\Exception\Exception;
use Swap\HistoricalExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\ExchangeRateQuery;
use Swap\Service\Xignite;

class XigniteTest extends ServiceTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "token" option must be provided.
     */
    public function it_throws_an_exception_if_token_option_missing()
    {
        new Xignite($this->getMock('Http\Client\HttpClient'));
    }

    /**
     * @test
     */
    public function it_support_all_queries()
    {
        $provider = new Xignite($this->getMock('Http\Client\HttpClient'), null, ['token' => 'token']);

        $this->assertTrue($provider->support(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'))));
        $this->assertTrue($provider->support(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_on_response_error()
    {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=GBPAWG&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=token';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/Xignite/error.json');

        $provider = new Xignite($this->getHttpAdapterMock($uri, $content), null, ['token' => 'token']);
        $caught = false;

        try {
            $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('GBP/AWG')));
        } catch (Exception $e) {
            $caught = true;
            $this->assertEquals('Error message', $e->getMessage());
        }

        $this->assertTrue($caught);
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=GBPAWG&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=token';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/Xignite/success.json');

        $provider = new Xignite($this->getHttpAdapterMock($uri, $content), null, ['token' => 'token']);
        $rate = $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('GBP/AWG')));

        $this->assertEquals('2.982308', $rate->getValue());
        $this->assertEquals(new \DateTime('2014-05-11 21:22:00', new \DateTimeZone('UTC')), $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate()
    {
        $uri = 'http://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetHistoricalRates?Symbols=EURUSD&AsOfDate=08/17/2016&_Token=token&FixingTime=&PriceType=Mid';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/Xignite/historical_success.json');

        $date = \DateTime::createFromFormat('m/d/Y', '08/17/2016', new \DateTimeZone('UTC'));
        $provider = new Xignite($this->getHttpAdapterMock($uri, $content), null, ['token' => 'token']);
        $rate = $provider->get(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), $date));

        $this->assertEquals('1.130228', $rate->getValue());
        $this->assertEquals($date, $rate->getDate());
    }
}