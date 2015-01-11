<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Exception;

use Swap\Model\CurrencyPair;

/**
 * Exception thrown when a currency pair is not supported by a provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedCurrencyPairException extends Exception
{
    private $currencyPair;

    public function __construct(CurrencyPair $currencyPair)
    {
        parent::__construct(sprintf('The currency pair "%s" is not supported.', $currencyPair->toString()));
        $this->currencyPair = $currencyPair;
    }

    /**
     * Gets the unsupported currency pair.
     *
     * @return \Swap\Model\CurrencyPair
     */
    public function getCurrencyPair()
    {
        return $this->currencyPair;
    }
}
