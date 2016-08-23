<?php
namespace Swap\Model;


/**
 * Represents a currency pair.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface CurrencyPairInterface
{
    /**
     * Gets the base currency.
     *
     * @return string
     */
    public function getBaseCurrency();

    /**
     * Gets the quote currency.
     *
     * @return string
     */
    public function getQuoteCurrency();

    /**
     * Check if the pair is identical.
     *
     * @return bool
     */
    public function isIdentical();

    /**
     * Returns a string representation of the pair.
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the hashed representation of the pair.
     *
     * @return string
     */
    public function toHash();
}
