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
 * Contract for providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface ProviderInterface
{
    /**
     * Fetches the rate for the currency pair.
     *
     * @param ExchangeQueryInterface $exchangeQuery
     *
     * @return \Swap\Model\Rate
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery);
}
