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
    private $baseCurrency;
    private $quoteCurrency;
    private $rate;
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
     * Creates a currency pair from a string.
     *
     * @param string $string A string in the form EUR/USD
     *
     * @return CurrencyPair
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromString($string)
    {
        $parts = explode('/', $string);

        if (!isset($parts[0]) || 3 !== strlen($parts[0]) || !isset($parts[1]) || 3 !== strlen($parts[1])) {
            throw new \InvalidArgumentException('The string must be in the form "EUR/USD".');
        }

        return new self($parts[0], $parts[1]);
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

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->rate;
    }
}
