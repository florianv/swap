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

use Swap\ExchangeQueryInterface;

/**
 * Exception thrown when an exchange query is not supported by a provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedExchangeQueryException extends Exception
{
    private $exchangeQuery;

    public function __construct(ExchangeQueryInterface $exchangeQuery)
    {
        parent::__construct(sprintf('The exchange query "%s" is not supported by the provider.', $exchangeQuery->getCurrencyPair()->__toString()));
        $this->exchangeQuery = $exchangeQuery;
    }

    /**
     * Gets the unsupported exchange query.
     *
     * @return ExchangeQueryInterface
     */
    public function getExchangeQuery()
    {
        return $this->exchangeQuery;
    }
}
