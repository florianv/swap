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

use Swap\Model\CurrencyPairInterface;

/**
 * Exception thrown when a currency pair is not supported by a provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedCurrencyPairException extends Exception
{
    private $currencyPair;

    public function __construct(CurrencyPairInterface $currencyPair)
    {
        parent::__construct(sprintf('The currency pair "%s" is not supported by the provider.', $currencyPair->__toString()));
        $this->currencyPair = $currencyPair;
    }

    /**
     * Gets the unsupported currency pair.
     *
     * @return CurrencyPairInterface
     */
    public function getCurrencyPair()
    {
        return $this->currencyPair;
    }
}
