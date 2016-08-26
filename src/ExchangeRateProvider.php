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
use Swap\Contract\ExchangeRateProvider as ExchangeRateProviderContract;
use Swap\Contract\ExchangeRateQuery as ExchangeRateQueryContract;
use Swap\Contract\ExchangeRateService as ExchangeRateServiceContract;
use Swap\Exception\UnsupportedExchangeQueryException;

/**
 * Default implementation of the exchange rate provider with PSR-6 caching support.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class ExchangeRateProvider implements ExchangeRateProviderContract
{
    private $service;
    private $cacheItemPool;
    private $options;

    public function __construct(ExchangeRateServiceContract $service, CacheItemPoolInterface $cacheItemPool = null, array $options = [])
    {
        $this->service = $service;
        $this->cacheItemPool = $cacheItemPool;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeRate(ExchangeRateQueryContract $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($currencyPair->isIdentical()) {
            return new ExchangeRate(1);
        }

        if (!$this->service->supportQuery($exchangeQuery)) {
            throw new UnsupportedExchangeQueryException($exchangeQuery, $this->service);
        }

        if (null === $this->cacheItemPool) {
            return $this->service->getExchangeRate($exchangeQuery);
        }

        $item = $this->cacheItemPool->getItem($currencyPair->toHash());

        if (!$exchangeQuery->getOption('refresh') && $item->isHit()) {
            return $item->get();
        }

        $rate = $this->service->getExchangeRate($exchangeQuery);

        $item->set($rate);
        $item->expiresAfter($exchangeQuery->getOption('cache_ttl', isset($this->options['cache_ttl']) ? $this->options['cache_ttl'] : null));

        $this->cacheItemPool->save($item);

        return $rate;
    }
}
