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

use Swap\ExchangeRate;
use Swap\ExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\Service\PhpArray;

class PhpArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new PhpArray([]);
        $this->assertFalse($provider->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'))));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\InternalException
     * @expectedExceptionMessage Rates passed to the ArrayProvider must be Rate instances or scalars "array" given.
     */
    public function it_throws_an_exception_when_fetching_invalid_rate()
    {
        $arrayProvider = new PhpArray([
            'EUR/USD' => [],
        ]);

        $arrayProvider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_from_rates()
    {
        $arrayProvider = new PhpArray([
            'EUR/USD' => $rate = new ExchangeRate('1.50'),
        ]);

        $this->assertSame($rate, $arrayProvider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'))));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_from_scalars()
    {
        $arrayProvider = new PhpArray([
            'EUR/USD' => 1.50,
            'USD/GBP' => '1.25',
            'JPY/GBP' => 1,
        ]);

        $eurUsd = $arrayProvider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));
        $usdGbp = $arrayProvider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('USD/GBP')));
        $jpyGbp = $arrayProvider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('JPY/GBP')));

        $this->assertEquals('1.50', $eurUsd->getValue());
        $this->assertEquals('1.25', $usdGbp->getValue());
        $this->assertEquals('1', $jpyGbp->getValue());
    }
}
