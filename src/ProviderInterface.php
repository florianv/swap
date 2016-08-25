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

use Swap\Model\RateInterface;

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
     * @return RateInterface
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery);

    /**
     * Tells if the provider supports the exchange query.
     *
     * @param ExchangeQueryInterface $exchangeQuery
     *
     * @return bool
     */
    public function support(ExchangeQueryInterface $exchangeQuery);
}
