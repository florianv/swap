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
use Swap\Service\CentralBankOfRepublicTurkey;

class CentralBankOfRepublicTurkeyTest extends ServiceTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new CentralBankOfRepublicTurkey($this->getMock('Http\Client\HttpClient'));

        $this->assertFalse($provider->support(new ExchangeRateQuery(CurrencyPair::createFromString('TRY/EUR'))));
        $this->assertFalse($provider->support(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/GBP'))));
        $this->assertFalse($provider->support(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('XXX/TRY'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported()
    {
        $url = 'http://www.tcmb.gov.tr/kurlar/today.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CentralBankOfRepublicTurkey/cbrt_today.xml');

        $provider = new CentralBankOfRepublicTurkey($this->getHttpAdapterMock($url, $content));
        $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('XXX/TRY')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'http://www.tcmb.gov.tr/kurlar/today.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CentralBankOfRepublicTurkey/cbrt_today.xml');

        $provider = new CentralBankOfRepublicTurkey($this->getHttpAdapterMock($url, $content));
        $rate = $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));

        $this->assertSame('3.2083', $rate->getValue());
        $this->assertEquals(new \DateTime('2016-03-15'), $rate->getDate());
    }
}
