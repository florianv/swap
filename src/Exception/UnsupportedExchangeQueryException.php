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
use Swap\Contract\ExchangeRateService;

/**
 * Exception thrown when an exchange query is not supported by a provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedExchangeQueryException extends Exception
{
    private $exchangeRateQuery;
    private $service;

    public function __construct(ExchangeRateQuery $exchangeRateQuery, ExchangeRateService $service)
    {
        parent::__construct(sprintf(
            'The exchange query "%s" is not supported by the service "%s".',
            $exchangeRateQuery->getCurrencyPair()->__toString(),
            get_class($service)
        ));

        $this->exchangeRateQuery = $exchangeRateQuery;
        $this->service = $service;
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

    /**
     * Gets the service.
     *
     * @return ExchangeRateService
     */
    public function getService()
    {
        return $this->service;
    }
}
