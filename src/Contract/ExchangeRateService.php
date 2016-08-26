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
 * Contract for exchange rate services.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface ExchangeRateService extends ExchangeRateProvider
{
    /**
     * Tells the service supports the exchange rate query.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return bool
     */
    public function supportQuery(ExchangeRateQuery $exchangeQuery);

    /**
     * Gets the unique service name.
     *
     * @return string
     */
    public function getName();
}
