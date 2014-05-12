<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Model;

/**
 * Contract for currency pairs.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface CurrencyPairInterface
{
    /**
     * Gets the base currency (ISO 4217 code).
     *
     * @return string
     */
    public function getBaseCurrency();

    /**
     * Gets the quote currency (ISO 4217 code).
     *
     * @return string
     */
    public function getQuoteCurrency();

    /**
     * Sets the rate.
     *
     * @param string $rate
     */
    public function setRate($rate);

    /**
     * Gets the rate.
     *
     * @return string|null
     */
    public function getRate();

    /**
     * Sets the date at which the rate was calculated.
     *
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date);

    /**
     * Gets the date at which the rate was calculated.
     *
     * @return \Datetime
     */
    public function getDate();
}
