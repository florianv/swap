<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Exception;

/**
 * Exception thrown when a currency is not supported as base.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedBaseCurrencyException extends \Exception
{
    /**
     * Creates a new exception.
     *
     * @param string $currency
     */
    public function __construct($currency)
    {
        parent::__construct(sprintf('The base currency "%s" is not supported.', $currency));
    }
}
