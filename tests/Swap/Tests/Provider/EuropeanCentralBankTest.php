<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Provider;

use Swap\Provider\EuropeanCentralBank;
use Swap\Model\CurrencyPair;

class EuropeanCentralBankTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedBaseCurrencyException
     */
    function it_throws_an_unsupported_base_currency_exception_when_base_is_not_euro()
    {
        $provider = new EuropeanCentralBank($this->getMock('Swap\AdapterInterface'));
        $provider->quote(array(new CurrencyPair('USD', 'EUR')));
    }

    /**
     * @test
     */
    function it_sets_bid_and_date_of_one_pair()
    {
        $uri = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/EuropeanCentralBank/daily.xml');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($body));

        $pair = new CurrencyPair('EUR', 'BGN');

        $provider = new EuropeanCentralBank($adapter);
        $provider->quote(array($pair));

        $this->assertSame('1.9558', $pair->getRate());
        $this->assertEquals(new \DateTime('2014-05-09'), $pair->getDate());
    }

    /**
     * @test
     */
    function it_sets_the_bid_and_date_of_three_pairs()
    {
        $uri = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/EuropeanCentralBank/daily.xml');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($body));

        $pairOne = new CurrencyPair('EUR', 'BGN');
        $pairTwo = new CurrencyPair('EUR', 'KRW');
        $pairThree = new CurrencyPair('EUR', 'RUB');

        $provider = new EuropeanCentralBank($adapter);
        $provider->quote(array($pairOne, $pairTwo, $pairThree));

        $this->assertSame('1.9558', $pairOne->getRate());
        $this->assertEquals(new \DateTime('2014-05-09'), $pairOne->getDate());

        $this->assertSame('1413.41', $pairTwo->getRate());
        $this->assertEquals(new \DateTime('2014-05-09'), $pairTwo->getDate());

        $this->assertSame('48.5270', $pairThree->getRate());
        $this->assertEquals(new \DateTime('2014-05-09'), $pairThree->getDate());
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    function it_throws_an_unsupported_currency_pair_if_the_pair_is_not_quoted()
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/EuropeanCentralBank/daily.xml');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($body));

        $pair = new CurrencyPair('EUR', 'XXX');

        $provider = new EuropeanCentralBank($adapter);
        $provider->quote(array($pair));
    }
}
