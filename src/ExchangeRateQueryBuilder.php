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

/**
 * Helps building exchange queries.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class ExchangeRateQueryBuilder
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
     * Adds an option.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Builds the query.
     *
     * @return \Swap\Contract\ExchangeRateQuery
     */
    public function build()
    {
        if (null === $this->currencyPair) {
            throw new \RuntimeException(sprintf('You need to set a currency pair using setCurrencyPair()'));
        }

        if ($this->date) {
            return new HistoricalExchangeRateQuery($this->currencyPair, $this->date, $this->options);
        }

        return new ExchangeRateQuery($this->currencyPair, $this->options);
    }
}
