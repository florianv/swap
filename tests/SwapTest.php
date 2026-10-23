<?php

declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Swap\Swap;

final class SwapTest extends TestCase
{
    #[Test]
    public function latest(): void
    {
        $exchangeRateProvider = $this->createMock(ExchangeRateProvider::class);

        $exchangeRateProvider
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with($this->callback(function (mixed $exchangeRateQuery): bool {
                return $exchangeRateQuery instanceof ExchangeRateQuery;
            }));

        $swap = new Swap($exchangeRateProvider);
        $swap->latest('EUR/USD');
    }

    #[Test]
    public function historical(): void
    {
        $exchangeRateProvider = $this->createMock(ExchangeRateProvider::class);

        $exchangeRateProvider
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with($this->callback(function (mixed $exchangeRateQuery): bool {
                return $exchangeRateQuery instanceof HistoricalExchangeRateQuery;
            }));

        $swap = new Swap($exchangeRateProvider);
        $swap->historical('EUR/USD', new \DateTime());
    }
}
