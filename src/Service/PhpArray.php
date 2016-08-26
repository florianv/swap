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
use Swap\Service\Traits\GetName;

/**
 * Service that retrieves rates from an array.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class PhpArray implements ExchangeRateService
{
    use GetName;

    /**
     * The rates.
     *
     * @var ExchangeRate[]
     */
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
    public function getExchangeRate(ExchangeRateQuery $exchangeQuery)
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
    public function supportQuery(ExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        return !$exchangeQuery instanceof HistoricalExchangeRateQuery
        && isset($this->rates[$currencyPair->__toString()]);
    }
}
