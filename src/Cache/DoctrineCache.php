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

use Doctrine\Common\Cache\Cache;
use Swap\CacheInterface;
use Swap\Model\QuotationRequest;
use Swap\Model\Rate;

/**
 * DoctrineCache implementation.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class DoctrineCache implements CacheInterface
{
    private $cache;
    private $ttl;

    /**
     * Creates a new cache.
     *
     * @param Cache $cache The cache
     * @param int   $ttl   The ttl in seconds
     */
    public function __construct(Cache $cache, $ttl = 0)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(QuotationRequest $request)
    {
        $rate = $this->cache->fetch($request->toString());

        return false === $rate ? null : $rate;
    }

    /**
     * {@inheritdoc}
     */
    public function storeRate(QuotationRequest $request, Rate $rate)
    {
        $this->cache->save($request->toString(), $rate, $this->ttl);
    }
}
