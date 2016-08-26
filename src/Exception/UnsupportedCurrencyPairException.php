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

use Swap\Contract\CurrencyPair;
use Swap\Contract\ExchangeRateService;

/**
 * Exception thrown when a currency pair is not supported by a service.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedCurrencyPairException extends Exception
{
    private $currencyPair;
    private $service;

    public function __construct(CurrencyPair $currencyPair, ExchangeRateService $service)
    {
        parent::__construct(
            sprintf(
                'The currency pair "%s" is not supported by the service "%s".',
                $currencyPair->__toString(),
                get_class($service)
            )
        );

        $this->currencyPair = $currencyPair;
        $this->service = $service;
    }

    /**
     * Gets the unsupported currency pair.
     *
     * @return CurrencyPair
     */
    public function getCurrencyPair()
    {
        return $this->currencyPair;
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
