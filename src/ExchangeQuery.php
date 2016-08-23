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

use Swap\Model\CurrencyPair;

/**
 * Default exchange query implementation.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class ExchangeQuery implements ExchangeQueryInterface
{
    /**
     * The currency pair.
     *
     * @var CurrencyPair
     */
    private $currencyPair;

    /**
     * Creates a new request.
     *
     * @param CurrencyPair $currencyPair
     */
    public function __construct(CurrencyPair $currencyPair)
    {
        $this->currencyPair = $currencyPair;
    }

    /**
     * Creates a new request from a string.
     *
     * @param string $currencyPair
     *
     * @return ExchangeQueryInterface
     */
    public static function createFromString($currencyPair)
    {
        return new static(CurrencyPair::createFromString($currencyPair));
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyPair()
    {
        return $this->currencyPair;
    }
}
