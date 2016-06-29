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

    /**
     * Factory to instantiate QuotationRequest
     *
     * @param string|CurrencyPair $currencyPair
     * @param \DateTime|null $dateTime
     * @return QuotationRequest
     *
     * @throws \InvalidArgumentException
     */
    public static function create(CurrencyPair $currencyPair, \DateTime $dateTime = NULL)
    {
        if (is_string($currencyPair)) {
            $currencyPair = CurrencyPair::createFromString($currencyPair);
        } elseif (!($currencyPair instanceof CurrencyPair)) {
            throw new \InvalidArgumentException(
                'The currency pair must be either a string or an instance of CurrencyPair'
            );
        }

        return new QuotationRequest($currencyPair, $dateTime);
    }
}
