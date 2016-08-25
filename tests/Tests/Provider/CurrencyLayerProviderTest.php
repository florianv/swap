<?php
/*
 * This file is part of Swap.
 *
 * (c) Pascal Hofmann <mail@pascalhofmann.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Provider;

use Swap\ExchangeQuery;
use Swap\HistoricalExchangeQuery;
use Swap\Model\CurrencyPair;
use Swap\Provider\CurrencyLayerProvider;

class CurrencyLayerProviderTest extends AbstractProviderTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "access_key" option must be provided.
     */
    public function it_throws_an_exception_if_access_key_option_missing()
    {
        new CurrencyLayerProvider($this->getMock('Http\Client\HttpClient'));
    }

    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new CurrencyLayerProvider($this->getMock('Http\Client\HttpClient'), null, ['access_key' => 'secret']);
        $this->assertFalse($provider->support(new ExchangeQuery(CurrencyPair::createFromString('EUR/EUR'))));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_an_exception_with_error_response()
    {
        $uri = 'http://www.apilayer.net/api/live?access_key=secret&currencies=EUR';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/error.json');

        $provider = new CurrencyLayerProvider($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret']);
        $provider->fetchRate(new ExchangeQuery(CurrencyPair::createFromString('USD/EUR')));
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

        $provider = new CurrencyLayerProvider($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret']);
        $rate = $provider->fetchRate(new ExchangeQuery(CurrencyPair::createFromString('USD/EUR')));

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

        $provider = new CurrencyLayerProvider($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret', 'enterprise' => true]);
        $rate = $provider->fetchRate(new ExchangeQuery(CurrencyPair::createFromString('USD/EUR')));

        $this->assertEquals('0.726804', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate_normal_mode()
    {
        $uri = 'http://apilayer.net/api/historical?access_key=secret&date=2015-05-06';
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1430870399);
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/historical_success.json');

        $provider = new CurrencyLayerProvider($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret']);
        $rate = $provider->fetchRate(new HistoricalExchangeQuery(CurrencyPair::createFromString('USD/AED'), (new \DateTime())->setTimestamp(1430870399)));

        $this->assertEquals('3.673069', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate_enterprise_mode()
    {
        $uri = 'http://apilayer.net/api/historical?access_key=secret&date=2015-05-06&source=USD';
        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1430870399);
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/historical_success.json');

        $provider = new CurrencyLayerProvider($this->getHttpAdapterMock($uri, $content), null, ['access_key' => 'secret', 'enterprise' => true]);
        $rate = $provider->fetchRate(new HistoricalExchangeQuery(CurrencyPair::createFromString('USD/AED'), (new \DateTime())->setTimestamp(1430870399)));

        $this->assertEquals('3.673069', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }
}
