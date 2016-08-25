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

use Swap\Model\CurrencyPairInterface;

/**
 * Default historical exchange query implementation.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class HistoricalExchangeQuery extends ExchangeQuery implements HistoricalExchangeQueryInterface
{
    /**
     * Gets the date.
     *
     * @var \DateTime
     */
    private $date;

    /**
     * Creates a new query.
     *
     * @param CurrencyPairInterface $currencyPair
     * @param \DateTime             $date
     * @param array                 $options
     */
    public function __construct(CurrencyPairInterface $currencyPair, \DateTime $date, array $options = [])
    {
        parent::__construct($currencyPair, $options);

        $this->date = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        return $this->date;
    }
}
