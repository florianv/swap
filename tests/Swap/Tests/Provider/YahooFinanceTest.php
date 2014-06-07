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
use Swap\Provider\YahooFinance;

class YahooFinanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_sets_bid_and_date_of_one_pair()
    {
        $uri = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/success_one.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($body));

        $pair = new CurrencyPair('EUR', 'USD');

        $provider = new YahooFinance($adapter);
        $provider->quote(array($pair));

        $this->assertSame('1.3758', $pair->getRate());
        $this->assertEquals(new \DateTime('2014-05-10 07:23:00'), $pair->getDate());
    }

    /**
     * @test
     */
    function it_sets_the_bid_and_date_of_three_pairs()
    {
        $uri = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%2C%22USDGBP%22%2C%22GBPEUR%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/success_three.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->will($this->returnValue($body));

        $pairOne = new CurrencyPair('EUR', 'USD');
        $pairTwo = new CurrencyPair('USD', 'GBP');
        $pairThree = new CurrencyPair('GBP', 'EUR');

        $provider = new YahooFinance($adapter);
        $provider->quote(array($pairOne, $pairTwo, $pairThree));

        $this->assertSame('1.3758', $pairOne->getRate());
        $this->assertEquals(new \DateTime('2014-05-10 07:23:00'), $pairOne->getDate());

        $this->assertSame('0.5935', $pairTwo->getRate());
        $this->assertEquals(new \DateTime('2014-05-10 20:23:00'), $pairTwo->getDate());

        $this->assertSame('1.2248', $pairThree->getRate());
        $this->assertEquals(new \DateTime('2014-05-12 07:23:00'), $pairThree->getDate());
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    function it_throws_exception_when_currency_pair_is_not_supported()
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/unsupported.json');

        $adapter = $this->getMock('Swap\AdapterInterface');
        $adapter
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($body));

        $pair = new CurrencyPair('EUR', 'XXX');

        $provider = new YahooFinance($adapter);
        $provider->quote(array($pair));
    }
}
