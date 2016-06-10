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
use Swap\Model\Rate;
use Swap\Provider\ArrayProvider;

class ArrayProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_an_exception_when_rate_not_supported()
    {
        $arrayProvider = new ArrayProvider([]);
        $arrayProvider->fetchRate(CurrencyPair::createFromString('EUR/USD'));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\InternalException
     * @expectedExceptionMessage Rates passed to the ArrayProvider must be Rate instances or scalars "array" given.
     */
    public function it_throws_an_exception_when_fetching_invalid_rate()
    {
        $arrayProvider = new ArrayProvider([
            'EUR/USD' => array(),
        ]);

        $arrayProvider->fetchRate(CurrencyPair::createFromString('EUR/USD'));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_from_rates()
    {
        $arrayProvider = new ArrayProvider([
            'EUR/USD' => $rate = new Rate('1.50'),
        ]);

        $this->assertSame($rate, $arrayProvider->fetchRate(CurrencyPair::createFromString('EUR/USD')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_from_scalars()
    {
        $arrayProvider = new ArrayProvider([
            'EUR/USD' => 1.50,
            'USD/GBP' => '1.25',
            'JPY/GBP' => 1,
        ]);

        $eurUsd = $arrayProvider->fetchRate(CurrencyPair::createFromString('EUR/USD'));
        $usdGbp = $arrayProvider->fetchRate(CurrencyPair::createFromString('USD/GBP'));
        $jpyGbp = $arrayProvider->fetchRate(CurrencyPair::createFromString('JPY/GBP'));

        $this->assertEquals('1.50', $eurUsd->getValue());
        $this->assertEquals('1.25', $usdGbp->getValue());
        $this->assertEquals('1', $jpyGbp->getValue());
    }
}
