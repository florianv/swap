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

use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\ExchangeQueryInterface;
use Swap\HistoricalExchangeQueryInterface;
use Swap\Model\CurrencyPairInterface;
use Swap\Model\Rate;

/**
 * Base class for historical providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractHistoricalProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if (!$this->supportsCurrencyPair($currencyPair)) {
            throw new UnsupportedCurrencyPairException($currencyPair);
        }

        if ($exchangeQuery instanceof HistoricalExchangeQueryInterface) {
            if ($rate = $this->fetchHistoricalRate($exchangeQuery)) {
                return $rate;
            }
        } elseif ($rate = $this->fetchLatestRate($exchangeQuery)) {
            return $rate;
        }

        throw new UnsupportedCurrencyPairException($currencyPair);
    }

    /**
     * Tells whether the provider supports the currency pair.
     *
     * @param CurrencyPairInterface $currencyPair
     *
     * @return bool
     */
    protected function supportsCurrencyPair(CurrencyPairInterface $currencyPair)
    {
        return true;
    }

    /**
     * Fetches the latest rate.
     *
     * @param ExchangeQueryInterface $exchangeQuery
     *
     * @return Rate|null
     */
    abstract protected function fetchLatestRate(ExchangeQueryInterface $exchangeQuery);

    /**
     * Fetches an historical rate.
     *
     * @param HistoricalExchangeQueryInterface $exchangeQuery
     *
     * @return Rate|null
     */
    abstract protected function fetchHistoricalRate(HistoricalExchangeQueryInterface $exchangeQuery);
}
