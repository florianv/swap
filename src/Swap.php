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
use Swap\Exception\UnsupportedExchangeQueryException;
use Swap\Model\Rate;

/**
 * An implementation of Swap.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Swap implements SwapInterface
{
    private $provider;
    private $cacheItemPool;
    private $options;

    public function __construct(ProviderInterface $provider, CacheItemPoolInterface $cacheItemPool = null, array $options = [])
    {
        $this->provider = $provider;
        $this->cacheItemPool = $cacheItemPool;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($currencyPair->isIdentical()) {
            return new Rate(1);
        }

        if (!$this->provider->support($exchangeQuery)) {
            throw new UnsupportedExchangeQueryException($exchangeQuery);
        }

        if (null === $this->cacheItemPool || $exchangeQuery->getOption('cache_disabled')) {
            return $this->provider->fetchRate($exchangeQuery);
        }

        $item = $this->cacheItemPool->getItem($currencyPair->toHash());

        if (!$exchangeQuery->getOption('refresh') && $item->isHit()) {
            return $item->get();
        }

        $rate = $this->provider->fetchRate($exchangeQuery);

        $item->set($rate);
        $item->expiresAfter($exchangeQuery->getOption('cache_ttl', isset($this->options['cache_ttl']) ? $this->options['cache_ttl'] : null));

        $this->cacheItemPool->save($item);

        return $rate;
    }
}
