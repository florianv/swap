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
 * Contract for exchange rate service providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface ExchangeRateService
{
    /**
     * Gets the exchange rate for the query.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate
     */
    public function get(ExchangeRateQuery $exchangeQuery);

    /**
     * Tells if it supports the exchange query.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return bool
     */
    public function support(ExchangeRateQuery $exchangeQuery);
}
