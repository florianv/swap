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
    private $cacheTtl;

    public function __construct(ProviderInterface $provider, CacheItemPoolInterface $cacheItemPool = null, $cacheTtl = 0)
    {
        $this->provider = $provider;
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheTtl = $cacheTtl;
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

        if (null === $this->cacheItemPool) {
            return $this->provider->fetchRate($exchangeQuery);
        }

        $item = $this->cacheItemPool->getItem($currencyPair->toHash());

        if ($item->isHit()) {
            return $item->get();
        }

        $rate = $this->provider->fetchRate($exchangeQuery);

        $item->set($rate);
        $item->expiresAfter($this->cacheTtl);

        $this->cacheItemPool->save($item);

        return $rate;
    }
}
