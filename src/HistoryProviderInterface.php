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

use Swap\Model\QuotationRequest;

/**
 * Contract for providers retrieving historical data.
 *
 * @author Petr Kramar <petr.kramar@perlur.cz>
 */
interface HistoryProviderInterface
{
    /**
     * Fetches the historical rate for the currency pair.
     *
     * @param CurrencyPair $currencyPair
     * @param \DateTime    $date
     *
     * @return \Swap\Model\Rate|null
     */
    public function fetchHistoryRate(CurrencyPair $currencyPair, \DateTime $date);
}
