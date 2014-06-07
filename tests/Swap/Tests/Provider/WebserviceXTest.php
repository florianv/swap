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
use Swap\Provider\WebserviceX;

class WebserviceXTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_sets_bid_and_date_of_one_pair()
    {
        $uri = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=USD';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_1.xml');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getAll')
            ->with(array($uri))
            ->will($this->returnValue(array($body)));

        $pair = new CurrencyPair('EUR', 'USD');

        $provider = new WebserviceX($adapter);
        $provider->quote(array($pair));

        $this->assertSame('1.3608', $pair->getRate());
        $this->assertInstanceOf('\DateTime', $pair->getDate());
    }

    /**
     * @test
     */
    function it_sets_the_bid_and_date_of_three_pairs()
    {
        $uriOne = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=USD';
        $bodyOne = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_1.xml');

        $uriTwo = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=BOB';
        $bodyTwo = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_2.xml');

        $uriThree = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=USD&ToCurrency=BOB';
        $bodyThree = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_3.xml');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getAll')
            ->with(array($uriOne, $uriTwo, $uriThree))
            ->will($this->returnValue(array($bodyOne, $bodyTwo, $bodyThree)));

        $pairOne = new CurrencyPair('EUR', 'USD');
        $pairTwo = new CurrencyPair('EUR', 'BOB');
        $pairThree = new CurrencyPair('USD', 'BOB');

        $provider = new WebserviceX($adapter);
        $provider->quote(array($pairOne, $pairTwo, $pairThree));

        $this->assertSame('1.3608', $pairOne->getRate());
        $this->assertInstanceOf('\DateTime', $pairOne->getDate());

        $this->assertSame('2.5000', $pairTwo->getRate());
        $this->assertInstanceOf('\DateTime', $pairTwo->getDate());

        $this->assertSame('3.5000', $pairThree->getRate());
        $this->assertInstanceOf('\DateTime', $pairThree->getDate());
    }
}
