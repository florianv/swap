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

use Swap\Model\CurrencyPair;

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
     * @param CurrencyPair $currencyPair
     *
     * @return \Swap\Model\Rate
     */
    public function fetchRate(CurrencyPair $currencyPair);
}
