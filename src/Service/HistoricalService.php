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

use Swap\Contract\ExchangeRate;
use Swap\Contract\ExchangeRateQuery;
use Swap\Contract\HistoricalExchangeRateQuery;
use Swap\Exception\UnsupportedCurrencyPairException;

/**
 * Base class for historical providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class HistoricalService extends Service
{
    /**
     * {@inheritdoc}
     */
    public function get(ExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($exchangeQuery instanceof HistoricalExchangeRateQuery) {
            if ($rate = $this->getHistorical($exchangeQuery)) {
                return $rate;
            }
        } elseif ($rate = $this->getLatest($exchangeQuery)) {
            return $rate;
        }

        throw new UnsupportedCurrencyPairException($currencyPair);
    }

    /**
     * Fetches the latest rate.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate|null
     */
    abstract protected function getLatest(ExchangeRateQuery $exchangeQuery);

    /**
     * Fetches an historical rate.
     *
     * @param HistoricalExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate|null
     */
    abstract protected function getHistorical(HistoricalExchangeRateQuery $exchangeQuery);
}
