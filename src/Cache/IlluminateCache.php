<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Cache;

use Illuminate\Contracts\Cache\Store;
use Swap\CacheInterface;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;

/**
 * IlluminateCache implementation.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class IlluminateCache implements CacheInterface
{
    private $store;
    private $ttl;

    /**
     * Creates a new Illuminate cache.
     *
     * @param Store $store The cache store
     * @param int   $ttl   The ttl in minutes
     */
    public function __construct(Store $store, $ttl = 0)
    {
        $this->store = $store;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(CurrencyPair $currencyPair)
    {
        return $this->store->get($currencyPair->toString());
    }

    /**
     * {@inheritdoc}
     */
    public function storeRate(CurrencyPair $currencyPair, Rate $rate)
    {
        $this->store->put($currencyPair->toString(), $rate, $this->ttl);
    }
}
