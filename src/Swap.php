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

use Swap\Model\CurrencyPair;
use Swap\Model\Rate;

/**
 * An implementation of Swap.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Swap implements SwapInterface
{
    private $provider;
    private $cache;

    public function __construct(ProviderInterface $provider, CacheInterface $cache = null)
    {
        $this->provider = $provider;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function quote($currencyPair)
    {
        if (is_string($currencyPair)) {
            $currencyPair = CurrencyPair::createFromString($currencyPair);
        } elseif (!$currencyPair instanceof CurrencyPair) {
            throw new \InvalidArgumentException(
                'The currency pair must be either a string or an instance of CurrencyPair'
            );
        }

        if (null !== $this->cache && null !== $rate = $this->cache->fetchRate($currencyPair)) {
            return $rate;
        }

        if ($currencyPair->isIdentical()) {
            $rate = new Rate(1);
        } else {
            $rate = $this->provider->fetchRate($currencyPair);
        }

        if (null !== $this->cache) {
            $this->cache->storeRate($currencyPair, $rate);
        }

        return $rate;
    }
}
