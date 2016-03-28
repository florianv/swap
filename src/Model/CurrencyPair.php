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
 * Represents a currency pair.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class CurrencyPair
{
    private $baseCurrency;
    private $quoteCurrency;

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
            throw new \InvalidArgumentException('The currency pair must be in the form "EUR/USD".');
        }

        return new self($parts[0], $parts[1]);
    }

    /**
     * Gets the base currency.
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    /**
     * Gets the quote currency.
     *
     * @return string
     */
    public function getQuoteCurrency()
    {
        return $this->quoteCurrency;
    }

    /**
     * Check if the pair is identical.
     *
     * @return bool
     */
    public function isIdentical()
    {
        return $this->baseCurrency === $this->quoteCurrency;
    }

    /**
     * Returns a string representation of the pair.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf('%s/%s', $this->baseCurrency, $this->quoteCurrency);
    }

    /**
     * Returns a string representation of the pair.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
