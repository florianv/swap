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
     * Quotes the currency pair.
     *
     * @param \Swap\Model\CurrencyPair|string
     *
     * @return \Swap\Model\Rate
     */
    public function quote($currencyPair);
}
