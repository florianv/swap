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
use Swap\Provider\GoogleFinance;

class GoogleFinanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_sets_bid_and_date_of_one_pair()
    {
        $uri = 'http://google.com/finance/converter?a=1&from=USD&to=GBP';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_usd_gbp.html');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getAll')
            ->with(array($uri))
            ->will($this->returnValue(array($body)));

        $pair = new CurrencyPair('USD', 'GBP');

        $provider = new GoogleFinance($adapter);
        $provider->quote(array($pair));

        $this->assertSame('0.5937', $pair->getRate());
        $this->assertInstanceOf('\DateTime', $pair->getDate());
    }

    /**
     * @test
     */
    function it_sets_the_bid_and_date_of_three_pairs()
    {
        $uriOne = 'http://google.com/finance/converter?a=1&from=CHF&to=COP';
        $bodyOne = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_chf_cop.html');
        $pairOne = new CurrencyPair('CHF', 'COP');

        $uriTwo = 'http://google.com/finance/converter?a=1&from=EUR&to=USD';
        $bodyTwo = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_eur_usd.html');
        $pairTwo = new CurrencyPair('EUR', 'USD');

        $uriThree = 'http://google.com/finance/converter?a=1&from=USD&to=GBP';
        $bodyThree = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_usd_gbp.html');
        $pairThree = new CurrencyPair('USD', 'GBP');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getAll')
            ->with(array($uriOne, $uriTwo, $uriThree))
            ->will($this->returnValue(array($bodyOne, $bodyTwo, $bodyThree)));

        $provider = new GoogleFinance($adapter);
        $provider->quote(array($pairOne, $pairTwo, $pairThree));

        $this->assertSame('2146.0437', $pairOne->getRate());
        $this->assertInstanceOf('\DateTime', $pairOne->getDate());

        $this->assertSame('1.3746', $pairTwo->getRate());
        $this->assertInstanceOf('\DateTime', $pairTwo->getDate());

        $this->assertSame('0.5937', $pairThree->getRate());
        $this->assertInstanceOf('\DateTime', $pairThree->getDate());
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    function it_throws_unsupported_currency_pair_when_rate_cannot_be_parsed()
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/invalid_convert_eur_all.html');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue(array($body)));

        $provider = new GoogleFinance($adapter);
        $provider->quote(array(new CurrencyPair('EUR', 'ALL')));
    }
}
