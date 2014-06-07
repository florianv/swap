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
use Swap\Provider\OpenExchangeRates;

class OpenExchangeRatesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_invalid_argument_exception_if_base_is_not_usd_and_non_enterprise_mode()
    {
        $provider = new OpenExchangeRates($this->getMock('Adapter\AdapterInterface'), 'secret');
        $provider->quote(array(new CurrencyPair('EUR', 'EUR')));
    }

    /**
     * @test
     */
    function it_sets_bid_and_date_of_four_pairs_with_different_base_enterprise_mode()
    {
        $uriOne = 'https://openexchangerates.org/api/latest.json?app_id=secret&base=EUR&symbols=AED,AFN';
        $bodyOne = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success_eur.json');

        $uriTwo = 'https://openexchangerates.org/api/latest.json?app_id=secret&base=USD&symbols=ALL,AMD';
        $bodyTwo = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success_usd.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getAll')
            ->with(array($uriOne, $uriTwo))
            ->will($this->returnValue(array($bodyOne, $bodyTwo)));

        $pairOne = new CurrencyPair('EUR', 'AED');
        $pairTwo = new CurrencyPair('EUR', 'AFN');
        $pairThree = new CurrencyPair('USD', 'ALL');
        $pairFour = new CurrencyPair('USD', 'AMD');

        $provider = new OpenExchangeRates($adapter, 'secret', true);
        $provider->quote(array($pairOne, $pairTwo, $pairThree, $pairFour));

        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1399748450);

        $this->assertSame('3.672947', $pairOne->getRate());
        $this->assertEquals($expectedDate, $pairOne->getDate());

        $this->assertSame('56.777675', $pairTwo->getRate());
        $this->assertEquals($expectedDate, $pairTwo->getDate());

        $this->assertSame('101.669799', $pairThree->getRate());
        $this->assertEquals($expectedDate, $pairThree->getDate());

        $this->assertSame('416.085', $pairFour->getRate());
        $this->assertEquals($expectedDate, $pairFour->getDate());
    }

    /**
     * @test
     */
    function it_sets_bid_and_date_of_three_pairs_non_enterprise()
    {
        $uri = 'https://openexchangerates.org/api/latest.json?app_id=secret';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success_usd.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getAll')
            ->with(array($uri))
            ->will($this->returnValue(array($body)));

        $pairOne = new CurrencyPair('USD', 'EUR');
        $pairTwo = new CurrencyPair('USD', 'GBP');
        $pairThree = new CurrencyPair('USD', 'HKD');

        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1399748450);

        $provider = new OpenExchangeRates($adapter, 'secret');
        $provider->quote(array($pairOne, $pairTwo, $pairThree));

        $this->assertSame('0.726804', $pairOne->getRate());
        $this->assertEquals($expectedDate, $pairOne->getDate());

        $this->assertSame('0.593408', $pairTwo->getRate());
        $this->assertEquals($expectedDate, $pairTwo->getDate());

        $this->assertSame('7.751676', $pairThree->getRate());
        $this->assertEquals($expectedDate, $pairThree->getDate());
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    function it_throws_an_unsupported_currency_pair_exception_when_rate_not_present()
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success_usd.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue(array($body)));

        $expectedDate = new \DateTime();
        $expectedDate->setTimestamp(1399748450);

        $pairOne = new CurrencyPair('USD', 'EUR');
        $pairTwo = new CurrencyPair('USD', 'XXX');

        $provider = new OpenExchangeRates($adapter, 'secret');
        $provider->quote(array($pairOne, $pairTwo));

        $this->assertSame('0.726804', $pairOne->getRate());
        $this->assertEquals($expectedDate, $pairOne->getDate());
    }
}
