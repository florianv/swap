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

use Swap\Contract\ExchangeRateService;

/**
 * Exception thrown when a date is not supported by a provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class UnsupportedDateException extends Exception
{
    private $date;
    private $service;

    public function __construct(\DateTimeInterface $date, ExchangeRateService $service)
    {
        parent::__construct(
            sprintf(
                'The date "%s" is not supported by the service "%s".',
                $date->format('Y-m-d'),
                get_class($this->service)
            )
        );

        $this->service = $service;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getService()
    {
        return $this->service;
    }
}
