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

use Swap\Model\QuotationRequest;
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
    public function quote($request)
    {
        if (is_string($request)) {
            $currencyPair = CurrencyPair::createFromString($request);
            $request = QuotationRequest::create($currencyPair);
        } elseif ($request instanceof CurrencyPair) {
            $request = QuotationRequest::create($request);
        } elseif (!($request instanceof QuotationRequest)) {
            throw new \InvalidArgumentException(
                'The request must be either a string, instance of CurrencyPair or QuotationRequest'
            );
        }

        $currencyPair = null;

        if (null !== $this->cache && null !== $rate = $this->cache->fetchRate($request)) {
            return $rate;
        }

        if ($request->getCurrencyPair()->isIdentical()) {
            $rate = new Rate(1);
        } else {
            if ($request->getDateTime() instanceof \DateTime) {
                if (!($this->provider instanceof HistoryProviderInterface)) {
                    throw new \InvalidArgumentException(
                        'The provider does not support history requests, must implement Swap\HistoryProviderInterface'
                    );
                }

                $rate = $this->provider->fetchHistoryRate($request->getCurrencyPair(), $request->getDateTime());
            } else {
                $rate = $this->provider->fetchRate($request->getCurrencyPair());
            }
        }

        if (null !== $this->cache) {
            $this->cache->storeRate($request, $rate);
        }

        return $rate;
    }
}
