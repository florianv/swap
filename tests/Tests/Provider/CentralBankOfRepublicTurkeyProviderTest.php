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
use Swap\Provider\CentralBankOfRepublicTurkeyProvider;

class CentralBankOfRepublicTurkeyProviderTest extends AbstractProviderTestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new CentralBankOfRepublicTurkeyProvider($this->getMock('Http\Client\HttpClient'));

        $this->assertFalse($provider->support(new ExchangeQuery(CurrencyPair::createFromString('TRY/EUR'))));
        $this->assertFalse($provider->support(new ExchangeQuery(CurrencyPair::createFromString('EUR/GBP'))));
        $this->assertFalse($provider->support(new HistoricalExchangeQuery(CurrencyPair::createFromString('XXX/TRY'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported()
    {
        $url = 'http://www.tcmb.gov.tr/kurlar/today.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CentralBankOfRepublicTurkey/cbrt_today.xml');

        $provider = new CentralBankOfRepublicTurkeyProvider($this->getHttpAdapterMock($url, $content));
        $provider->fetchRate(new ExchangeQuery(CurrencyPair::createFromString('XXX/TRY')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'http://www.tcmb.gov.tr/kurlar/today.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/CentralBankOfRepublicTurkey/cbrt_today.xml');

        $provider = new CentralBankOfRepublicTurkeyProvider($this->getHttpAdapterMock($url, $content));
        $rate = $provider->fetchRate(new ExchangeQuery(CurrencyPair::createFromString('EUR/TRY')));

        $this->assertSame('3.2083', $rate->getValue());
        $this->assertEquals(new \DateTime('2016-03-15'), $rate->getDate());
    }
}
