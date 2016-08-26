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
use Swap\Service\WebserviceX;

class WebserviceXTest extends ServiceTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new WebserviceX($this->getMock('Http\Client\HttpClient'));

        $this->assertTrue($provider->support(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'))));
        $this->assertFalse($provider->support(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $uri = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=USD';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/WebserviceX/success.xml');

        $provider = new WebserviceX($this->getHttpAdapterMock($uri, $content));
        $rate = $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));

        $this->assertEquals('1.3608', $rate->getValue());
        $this->assertEquals(new \DateTime(), $rate->getDate());
    }
}
