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

use Swap\ExchangeQuery;
use Swap\HistoricalExchangeQuery;
use Swap\Model\CurrencyPair;
use Swap\Provider\WebserviceXProvider;

class WebserviceXProviderTest extends AbstractProviderTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new WebserviceXProvider($this->getMock('Http\Client\HttpClient'));

        $this->assertTrue($provider->support(new ExchangeQuery(CurrencyPair::createFromString('USD/EUR'))));
        $this->assertFalse($provider->support(new HistoricalExchangeQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $uri = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=USD';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/WebserviceX/success.xml');

        $provider = new WebserviceXProvider($this->getHttpAdapterMock($uri, $content));
        $rate = $provider->fetchRate(new ExchangeQuery(CurrencyPair::createFromString('EUR/USD')));

        $this->assertEquals('1.3608', $rate->getValue());
        $this->assertEquals(new \DateTime(), $rate->getDate());
    }
}
