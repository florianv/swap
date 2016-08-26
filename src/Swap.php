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
use Swap\Contract\ExchangeRateProvider;
use Swap\Contract\ExchangeRateQuery as ExchangeRateQueryContract;
use Swap\Contract\ExchangeRateService as ExchangeRateServiceContract;
use Swap\Exception\UnsupportedExchangeQueryException;

/**
 * An implementation of Swap.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Swap implements ExchangeRateProvider
{
    private $provider;
    private $cacheItemPool;
    private $options;

    public function __construct(ExchangeRateServiceContract $provider, CacheItemPoolInterface $cacheItemPool = null, array $options = [])
    {
        $this->provider = $provider;
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

        if (!$this->provider->support($exchangeQuery)) {
            throw new UnsupportedExchangeQueryException($exchangeQuery);
        }

        if (null === $this->cacheItemPool || $exchangeQuery->getOption('cache_disabled')) {
            return $this->provider->get($exchangeQuery);
        }

        $item = $this->cacheItemPool->getItem($currencyPair->toHash());

        if (!$exchangeQuery->getOption('refresh') && $item->isHit()) {
            return $item->get();
        }

        $rate = $this->provider->get($exchangeQuery);

        $item->set($rate);
        $item->expiresAfter($exchangeQuery->getOption('cache_ttl', isset($this->options['cache_ttl']) ? $this->options['cache_ttl'] : null));

        $this->cacheItemPool->save($item);

        return $rate;
    }
}
