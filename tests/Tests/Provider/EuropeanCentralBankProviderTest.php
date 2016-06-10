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

use Swap\Model\CurrencyPair;
use Swap\Provider\EuropeanCentralBankProvider;

class EuropeanCentralBankProviderTest extends AbstractProviderTestCase
{
    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_when_base_is_not_euro()
    {
        $provider = new EuropeanCentralBankProvider($this->getMock('Ivory\HttpAdapter\HttpAdapterInterface'));
        $provider->fetchRate(new CurrencyPair('USD', 'EUR'));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function it_throws_an_exception_when_the_pair_is_not_supported()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/EuropeanCentralBank/success.xml');

        $provider = new EuropeanCentralBankProvider($this->getHttpAdapterMock($url, $content));
        $provider->fetchRate(new CurrencyPair('EUR', 'XXL'));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/EuropeanCentralBank/success.xml');

        $provider = new EuropeanCentralBankProvider($this->getHttpAdapterMock($url, $content));
        $rate = $provider->fetchRate(new CurrencyPair('EUR', 'BGN'));

        $this->assertSame('1.9558', $rate->getValue());
        $this->assertEquals(new \DateTime('2015-01-07'), $rate->getDate());
    }
}
