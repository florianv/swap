<?php

/**
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
     * Quotes the currency pairs.
     *
     * @param Model\CurrencyPairInterface[] $pairs
     *
     * @throws Exception\UnsupportedCurrencyPairException
     * @throws Exception\UnsupportedBaseCurrencyException
     * @throws Exception\QuotationException
     */
    public function quote(array $pairs);
}
