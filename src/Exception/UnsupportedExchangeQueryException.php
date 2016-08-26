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

use Swap\Contract\ExchangeRateQuery;

/**
 * Exception thrown when an exchange query is not supported by a provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedExchangeQueryException extends Exception
{
    private $exchangeRateQuery;

    public function __construct(ExchangeRateQuery $exchangeRateQuery)
    {
        parent::__construct(sprintf(
            'The exchange query "%s" is not supported by the provider.',
            $exchangeRateQuery->getCurrencyPair()->__toString()
        ));

        $this->exchangeRateQuery = $exchangeRateQuery;
    }

    /**
     * Gets the unsupported exchange query.
     *
     * @return ExchangeRateQuery
     */
    public function getExchangeRateQuery()
    {
        return $this->exchangeRateQuery;
    }
}
