<?php
/**
 * This file is part of Swap.
 *
 * (c) Pascal Hofmann <mail@pascalhofmann.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Provider;

use Swap\ExchangeQuery;
use Swap\Provider\CurrencyLayerProvider;

class CurrencyLayerProviderTest extends AbstractProviderTestCase
{
    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_if_base_is_not_usd_and_non_enterprise_mode()
    {
        $provider = new CurrencyLayerProvider('secret', false, $this->getMock('Http\Client\HttpClient'));
        $provider->fetchRate(ExchangeQuery::createFromString('EUR/EUR'));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_an_exception_with_error_response()
    {
        $uri = 'http://www.apilayer.net/api/live?access_key=secret&currencies=EUR';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CurrencyLayer/error.json');

        $provider = new CurrencyLayerProvider('secret', false, $this->getHttpAdapterMock($uri, $content));
        $provider->fetchRate(ExchangeQuery::createFromString('USD/EUR'));
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

        $provider = new CurrencyLayerProvider('secret', false, $this->getHttpAdapterMock($uri, $content));
        $rate = $provider->fetchRate(ExchangeQuery::createFromString('USD/EUR'));

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

        $provider = new CurrencyLayerProvider('secret', true, $this->getHttpAdapterMock($uri, $content));
        $rate = $provider->fetchRate(ExchangeQuery::createFromString('USD/EUR'));

        $this->assertEquals('0.726804', $rate->getValue());
        $this->assertEquals($expectedDate, $rate->getDate());
    }
}
