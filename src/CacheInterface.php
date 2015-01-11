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
use Swap\Model\Rate;

/**
 * Contract for caches.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface CacheInterface
{
    /**
     * Fetches the rate.
     *
     * @param CurrencyPair $currencyPair
     *
     * @return Rate|null
     */
    public function fetchRate(CurrencyPair $currencyPair);

    /**
     * Stores the rate.
     *
     * @param CurrencyPair $currencyPair
     * @param Rate         $rate
     */
    public function storeRate(CurrencyPair $currencyPair, Rate $rate);
}
