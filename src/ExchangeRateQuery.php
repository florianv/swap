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

use Swap\Contract\CurrencyPair as CurrencyPairContract;
use Swap\Contract\ExchangeRateQuery as ExchangeRateQueryContract;

/**
 * Default exchange query implementation.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class ExchangeRateQuery implements ExchangeRateQueryContract
{
    /**
     * The currency pair.
     *
     * @var CurrencyPairContract
     */
    private $currencyPair;

    /**
     * An array of option.
     *
     * - cache_ttl:     The cache TTL for the exchange result (overrides global).
     * - cache_disabled Disable the cache for this query
     * - refresh:       Whether to refresh the Exchange Result even if not expired in cache.
     *                  This will also clear the runtime cache that comes with some providers.
     *
     * @var array
     */
    private $options;

    /**
     * Creates a new query.
     *
     * @param CurrencyPairContract $currencyPair
     * @param array                $options
     */
    public function __construct(CurrencyPairContract $currencyPair, array $options = [])
    {
        $this->currencyPair = $currencyPair;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyPair()
    {
        return $this->currencyPair;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }
}
