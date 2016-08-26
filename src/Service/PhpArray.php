<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Service;

use Swap\Contract\ExchangeRateQuery;
use Swap\Contract\ExchangeRateService;
use Swap\Contract\HistoricalExchangeRateQuery;
use Swap\Exception\InternalException;
use Swap\ExchangeRate;

/**
 * Provides rates from an array.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class PhpArray implements ExchangeRateService
{
    private $rates;

    /**
     * Constructor.
     *
     * @param ExchangeRate[]|string[] $rates An array of rates indexed by the corresponding currency pair symbol
     */
    public function __construct(array $rates)
    {
        $this->rates = $rates;
    }

    /**
     * {@inheritdoc}
     */
    public function get(ExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();
        $rate = $this->rates[$currencyPair->__toString()];

        if (is_scalar($rate)) {
            $rate = new ExchangeRate($rate);
        } elseif (!$rate instanceof ExchangeRate) {
            throw new InternalException(sprintf(
                'Rates passed to the ArrayProvider must be Rate instances or scalars "%s" given.',
                gettype($rate)
            ));
        }

        return $rate;
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        return !$exchangeQuery instanceof HistoricalExchangeRateQuery
        && isset($this->rates[$currencyPair->__toString()]);
    }
}
