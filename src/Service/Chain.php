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
use Swap\Exception\ChainException;
use Swap\Exception\InternalException;
use Swap\Service\Traits\GetName;

/**
 * A service using other services in a chain.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Chain implements ExchangeRateService
{
    use GetName;

    /**
     * The services.
     *
     * @var array
     */
    private $services;

    /**
     * Creates a new chain service.
     *
     * @param ExchangeRateService[] $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * Adds a service to the chain.
     *
     * @param ExchangeRateService $service
     */
    public function addService(ExchangeRateService $service)
    {
        $this->services[] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeRate(ExchangeRateQuery $exchangeQuery)
    {
        $exceptions = [];

        foreach ($this->services as $service) {
            if (!$service->supportQuery($exchangeQuery)) {
                continue;
            }

            try {
                return $service->getExchangeRate($exchangeQuery);
            } catch (\Exception $e) {
                if ($e instanceof InternalException) {
                    throw $e;
                }

                $exceptions[] = $e;
            }
        }

        throw new ChainException($exceptions);
    }

    /**
     * {@inheritdoc}
     */
    public function supportQuery(ExchangeRateQuery $exchangeQuery)
    {
        foreach ($this->services as $service) {
            if ($service->supportQuery($exchangeQuery)) {
                return true;
            }
        }

        return false;
    }
}
