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
class UnsupportedCurrencyPairException extends \InvalidArgumentException
{
    private $currencyPair;

    /**
     * Creates a new exception.
     *
     * @param CurrencyPairInterface $currencyPair
     */
    public function __construct(CurrencyPairInterface $currencyPair)
    {
        $message = sprintf(
            'The currency pair "%s/%s" is not supported.',
            $currencyPair->getBaseCurrency(),
            $currencyPair->getQuoteCurrency()
        );

        parent::__construct($message);
        $this->currencyPair = $currencyPair;
    }

    /**
     * Gets the unsupported currency pair.
     *
     * @return \Swap\Model\CurrencyPairInterface
     */
    public function getCurrencyPair()
    {
        return $this->currencyPair;
    }
}
