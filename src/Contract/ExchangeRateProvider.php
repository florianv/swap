<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Contract;

/**
 * Contract for exchange rate providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface ExchangeRateProvider
{
    /**
     * Gets the exchange rate.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate
     */
    public function getExchangeRate(ExchangeRateQuery $exchangeQuery);
}
