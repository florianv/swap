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
 * Exception thrown when a date is not supported by a provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedDateException extends Exception
{
    public function __construct(\DateTimeInterface $date)
    {
        parent::__construct(sprintf('The date "%s" is not supported by the provider.', $date->format('Y-m-d')));
    }
}
