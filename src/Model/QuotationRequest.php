<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Model;

/**
 * Represents a quotation request.
 *
 * @author Petr Kramar <petr.kramar@perlur.cz>
 */
final class QuotationRequest
{
    /**
     * @var Swap\CurrencyPair
     */
    private $currencyPair;

    /**
     * @var \DateTime
     */
    private $dateTime;

    /**
     * Creates a new currency pair.
     *
     * @param CurrencyPair $currencyPair  Currency pair to be retrieved.
     * @param \DateTime|null $dateTime    Used to get historical data, null means latest.
     */
    public function __construct(CurrencyPair $pair, \DateTime $dateTime = null)
    {
        $this->currencyPair = $pair;
        $this->dateTime = $dateTime;
    }

    /**
     * Gets currency pair
     *
     * @return CurrencyPair
     */
    public function getCurrencyPair()
    {
        return $this->currencyPair;
    }

    /**
     * Gets date time
     *
     * @return \DateTime|null
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}
