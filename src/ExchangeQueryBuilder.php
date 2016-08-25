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
 * Helps building exchange queries.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class ExchangeQueryBuilder
{
    /**
     * The currency pair.
     *
     * @var CurrencyPair
     */
    private $currencyPair;

    /**
     * The date.
     *
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * The options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Creates a new query builder.
     *
     * @param string $currencyPair
     */
    public function __construct($currencyPair)
    {
        $this->currencyPair = CurrencyPair::createFromString($currencyPair);
    }

    /**
     * Sets the date.
     *
     * @param \DateTimeInterface $date
     *
     * @return $this
     */
    public function setDate(\DateTimeInterface $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Sets the cache ttl.
     *
     * @return $this
     */
    public function disableCache()
    {
        $this->options['cache_disabled'] = true;

        return $this;
    }

    /**
     * Sets the cache ttl.
     *
     * @param string $ttl
     *
     * @return $this
     */
    public function setCacheTtl($ttl)
    {
        $this->options['cache_ttl'] = $ttl;

        return $this;
    }

    /**
     * Forces the result to be "fresh" (not cached).
     *
     * @return $this
     */
    public function mustBeFresh()
    {
        $this->options['refresh'] = true;

        return $this;
    }

    /**
     * Sets the date.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return $this
     */
    public function addOption($name, $default = null)
    {
        $this->options[$name] = $default;

        return $this;
    }

    /**
     * Builds the query.
     *
     * @return ExchangeQueryInterface
     */
    public function build()
    {
        if (null === $this->currencyPair) {
            throw new \RuntimeException(sprintf('You need to set a currency pair using setCurrencyPair()'));
        }

        if ($this->date) {
            return new HistoricalExchangeQuery($this->currencyPair, $this->date, $this->options);
        }

        return new ExchangeQuery($this->currencyPair, $this->options);
    }
}
