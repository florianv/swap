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

use Swap\Model\CurrencyPair;
use Swap\Provider\Xignite;

class XigniteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_sets_bid_and_date_of_one_pair()
    {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=GBPAWG&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=token';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/success_one.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($body));

        $pair = new CurrencyPair('GBP', 'AWG');

        $provider = new Xignite($adapter, 'token');
        $provider->quote(array($pair));

        $this->assertSame('2.982308', $pair->getRate());
        $this->assertEquals(new \DateTime('2014-05-11 21:22:00', new \DateTimeZone('UTC')), $pair->getDate());
    }

    /**
     * @test
     */
    function it_sets_the_bid_and_date_of_three_pairs()
    {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=EURUSD,AUDUSD,AEDAOA&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=secret';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/success_three.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($body));

        $pairOne = new CurrencyPair('EUR', 'USD');
        $pairTwo = new CurrencyPair('AUD', 'USD');
        $pairThree = new CurrencyPair('AED', 'AOA');

        $provider = new Xignite($adapter, 'secret');
        $provider->quote(array($pairOne, $pairTwo, $pairThree));

        $this->assertSame('1.3758', $pairOne->getRate());
        $this->assertEquals(new \DateTime('2014-05-11 20:54:10', new \DateTimeZone('UTC')), $pairOne->getDate());

        $this->assertSame('0.9355', $pairTwo->getRate());
        $this->assertEquals(new \DateTime('2014-10-10 11:23:10', new \DateTimeZone('UTC')), $pairTwo->getDate());

        $this->assertSame('26.55778', $pairThree->getRate());
        $this->assertEquals(new \DateTime('2014-07-10 21:20:00', new \DateTimeZone('UTC')), $pairThree->getDate());
    }

    /**
     * @test
     * @expectedException \Swap\Exception\QuotationException
     */
    function it_throws_a_quotation_exception_on_error_outcome()
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/error_success.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($body));

        $pairOne = new CurrencyPair('EUR', 'USD');
        $pairTwo = new CurrencyPair('EUR', 'XXX');

        $provider = new Xignite($adapter, 'secret');
        $provider->quote(array($pairOne, $pairTwo));

        $this->assertSame('1.37562', $pairOne->getRate());
        $this->assertEquals(new \DateTime('2014-05-11 21:18:50', new \DateTimeZone('UTC')), $pairOne->getDate());
    }
}
