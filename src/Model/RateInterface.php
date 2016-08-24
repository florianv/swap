<?php

namespace Swap\Model;

/**
 * Represents a rate.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface RateInterface
{
    /**
     * Gets the rate value.
     *
     * @return string
     */
    public function getValue();

    /**
     * Gets the date at which this rate was calculated.
     *
     * @return \Datetime
     */
    public function getDate();
}
