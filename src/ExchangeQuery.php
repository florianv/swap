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

/**
 * Default exchange query implementation.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class ExchangeQuery implements ExchangeQueryInterface
{
    /**
     * The currency pair.
     *
     * @var CurrencyPair
     */
    private $currencyPair;

    /**
     * An array of option.
     *
     * - cache_ttl: The cache TTL for the Exchange Result (overrides global).
     * - refresh:   Whether to refresh the Exchange Result even if not expired in cache.
     *              This will also clear the runtime cache that comes with some providers.
     *
     * @var array
     */
    private $options;

    /**
     * Creates a new request.
     *
     * @param CurrencyPair $currencyPair
     * @param array        $options
     */
    public function __construct(CurrencyPair $currencyPair, array $options = [])
    {
        $this->currencyPair = $currencyPair;
        $this->options = $options;
    }

    /**
     * Creates a new request from a string.
     *
     * @param string $currencyPair
     * @param array  $options
     *
     * @return ExchangeQueryInterface
     */
    public static function createFromString($currencyPair, array $options = [])
    {
        return new static(CurrencyPair::createFromString($currencyPair), $options);
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
