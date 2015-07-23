<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Provider;

use Swap\Exception\InternalException;
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\ProviderInterface;

/**
 * Provides rates from an array.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class ArrayProvider implements ProviderInterface
{
    private $rates;

    /**
     * Constructor.
     *
     * @param \Swap\Model\Rate[]|string[] $rates An array of rates indexed by the corresponding currency pair symbol
     */
    public function __construct(array $rates)
    {
        $this->rates = $rates;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(CurrencyPair $currencyPair)
    {
        if (!isset($this->rates[$currencyPair->toString()])) {
            throw new UnsupportedCurrencyPairException($currencyPair);
        }

        $rate = $this->rates[$currencyPair->toString()];

        if (is_scalar($rate)) {
            $rate = new Rate($rate);
        } elseif (!$rate instanceof Rate) {
            throw new InternalException(sprintf(
                'Rates passed to the ArrayProvider must be Rate instances or scalars "%s" given.',
                gettype($rate)
            ));
        }

        return $rate;
    }
}
