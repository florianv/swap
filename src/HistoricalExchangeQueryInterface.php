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
 * Contract for historical exchange queries.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface HistoricalExchangeQueryInterface extends ExchangeQueryInterface
{
    /**
     * Gets the date.
     *
     * @return \DateTimeInterface
     */
    public function getDate();
}
