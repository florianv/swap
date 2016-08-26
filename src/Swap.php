<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap;

use Psr\Cache\CacheItemPoolInterface;
use Swap\Service\Chain;
use Swap\Service\ServiceFactory;

/**
 * Swap is an easy to use facade to retrieve exchange rates from various services.
 * Use this if you don't use a framework integration.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Swap
{
    /**
     * The service.
     *
     * @var Chain
     */
    private $service;

    /**
     * Creates a new Swaap.
     *
     * @param CacheItemPoolInterface $cacheItemPool
     * @param array                  $options
     */
    private function __construct(CacheItemPoolInterface $cacheItemPool = null, array $options = [])
    {
        $this->service = new Chain([]);
        $this->exchangeRateProvider = new ExchangeRateProvider($this->service, $cacheItemPool, $options);
    }

    /**
     * Factory method.
     *
     * @param CacheItemPoolInterface $cacheItemPool
     * @param array                  $options
     *
     * @return Swap
     */
    public static function create(CacheItemPoolInterface $cacheItemPool = null, array $options = [])
    {
        return new static($cacheItemPool, $options);
    }

    /**
     * Adds a new service.
     *
     * @param string $serviceName
     * @param array  $options
     *
     * @return Swap
     */
    public function with($serviceName, array $options = [])
    {
        $this->service->addService(ServiceFactory::createService($serviceName, $options));

        return $this;
    }

    /**
     * Quotes a currency pair.
     *
     * @param string             $currencyPair The currency pair like "EUR/USD"
     * @param \DateTimeInterface $date         An optional date for historical rates
     * @param array              $options      An array of query options
     *
     * @return ExchangeRate
     */
    public function quote($currencyPair, \DateTimeInterface $date = null, array $options = [])
    {
        $exchangeQueryBuilder = new ExchangeRateQueryBuilder($currencyPair);

        if (null !== $date) {
            $exchangeQueryBuilder->setDate($date);
        }

        foreach ($options as $name => $value) {
            $exchangeQueryBuilder->addOption($name, $value);
        }

        $query = $exchangeQueryBuilder->build();

        return $this->exchangeRateProvider->getExchangeRate($query);
    }
}
