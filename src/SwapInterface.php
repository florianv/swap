<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap;

/**
 * Contract for the Swap service.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface SwapInterface
{
    /**
     * Gets the exchange rate.
     *
     * @param ExchangeQueryInterface $exchangeQuery
     *
     * @return \Swap\Model\Rate
     */
    public function getExchangeRate(ExchangeQueryInterface $exchangeQuery);
}
