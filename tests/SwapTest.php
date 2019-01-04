<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests;

use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\Contract\ExchangeRateProvider;
use Exchanger\Contract\HistoricalExchangeRateQuery;
use PHPUnit\Framework\TestCase;
use Swap\Swap;

class SwapTest extends TestCase
{
    public function testLatest()
    {
        $exchangeRateProvider = $this->createMock(ExchangeRateProvider::class);

        $exchangeRateProvider
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with($this->callback(function ($exchangeRateQuery) {
                return $exchangeRateQuery instanceof ExchangeRateQuery;
            }));

        $swap = new Swap($exchangeRateProvider);
        $swap->latest('EUR/USD');
    }

    public function testHistorical()
    {
        $exchangeRateProvider = $this->createMock(ExchangeRateProvider::class);

        $exchangeRateProvider
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with($this->callback(function ($exchangeRateQuery) {
                return $exchangeRateQuery instanceof HistoricalExchangeRateQuery;
            }));

        $swap = new Swap($exchangeRateProvider);
        $swap->historical('EUR/USD', new \DateTime());
    }
}
