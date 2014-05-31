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
 * Implementation of CurrencyPairInterface.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class CurrencyPair implements CurrencyPairInterface
{
    /**
     * The base currency ISO 4217 code.
     *
     * @var string
     */
    private $baseCurrency;

    /**
     * The quote currency ISO 4217 code.
     *
     * @var string
     */
    private $quoteCurrency;

    /**
     * The rate.
     *
     * @var string|null
     */
    private $rate;

    /**
     * The date.
     *
     * @var \DateTime
     */
    private $date;

    /**
     * Creates a new currency pair.
     *
     * @param string $baseCurrency  The base currency ISO 4217 code.
     * @param string $quoteCurrency The quote currency ISO 4217 code.
     */
    public function __construct($baseCurrency, $quoteCurrency)
    {
        $this->baseCurrency = $baseCurrency;
        $this->quoteCurrency = $quoteCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteCurrency()
    {
        return $this->quoteCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * {@inheritdoc}
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * {@inheritdoc}
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        return $this->date;
    }
}
