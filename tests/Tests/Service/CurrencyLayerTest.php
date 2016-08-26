<?php
/*
 * This file is part of Swap.
 *
 * (c) Pascal Hofmann <mail@pascalhofmann.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Service;

use Swap\ExchangeRateQuery;
use Swap\HistoricalExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\Service\CurrencyLayer;

class CurrencyLayerTest extends ServiceTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "access_key" option must be provided.
     */
    public function it_throws_an_exception_if_access_key_option_missing()
    {
        new CurrencyLayer($this->getMock('Http\Client\HttpClient'));
    }

    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new CurrencyLayer($this->getMock('Http\Client\HttpClient'), null, ['access_key' => 'secret']);
        $this->assertFalse($provider->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/EUR'))));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_an_exception_with_error_response()
    {
        $uri = 'http://www.apilayer.net/api/live?access_key=secret&currencies=EUR';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/error.json');

        $provider = new CurrencyLayer($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret']);
        $provider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_normal_mode()
    {
        $uri = 'http://www.apilayer.net/api/live?access_key=secret&currencies=EUR';
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1399748450);
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/success.json');

        $provider = new CurrencyLayer($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret']);
        $rate = $provider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));

        $this->assertEquals('0.726804', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_enterprise_mode()
    {
        $uri = 'https://www.apilayer.net/api/live?access_key=secret&source=USD&currencies=EUR';
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1399748450);
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/success.json');

        $provider = new CurrencyLayer($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret', 'enterprise' => true]);
        $rate = $provider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));

        $this->assertEquals('0.726804', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate_normal_mode()
    {
        $uri = 'http://apilayer.net/api/historical?access_key=secret&date=2015-05-06';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/historical_success.json');
        $date = new \DateTime('2015-05-06');
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1430870399);

        $provider = new CurrencyLayer($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret']);
        $rate = $provider->getExchangeRate(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/AED'), $date));

        $this->assertEquals('3.673069', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate_enterprise_mode()
    {
        $uri = 'https://apilayer.net/api/historical?access_key=secret&date=2015-05-06&source=USD';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/historical_success.json');
        $date = new \DateTime('2015-05-06');
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1430870399);

        $provider = new CurrencyLayer($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret', 'enterprise' => true]);
        $rate = $provider->getExchangeRate(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/AED'), $date));

        $this->assertEquals('3.673069', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }
}
