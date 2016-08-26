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
 * Contract for historical exchange rate queries.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface HistoricalExchangeRateQuery extends ExchangeRateQuery
{
    /**
     * Gets the date.
     *
     * @return \DateTimeInterface
     */
    public function getDate();
}
