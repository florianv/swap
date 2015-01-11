<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Exception;

/**
 * Exception thrown when a currency code is invalid.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class InvalidCurrencyCodeException extends Exception
{
    public function __construct($currencyCode)
    {
        parent::__construct(sprintf('The currency code "%s" is invalid', $currencyCode));
    }
}
