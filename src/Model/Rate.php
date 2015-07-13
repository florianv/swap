<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Model;

/**
 * Represents a rate.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Rate
{
    private $value;
    private $date;

    /**
     * Creates a new rate.
     *
     * @param string         $value The rate value
     * @param \DateTime|null $date  The date at which this rate was calculated
     */
    public function __construct($value, \DateTime $date = null)
    {
        $this->value = (string) $value;
        $this->date = $date ?: new \DateTime();
    }

    /**
     * Gets the rate value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets the date at which this rate was calculated.
     *
     * @return \Datetime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Returns the rate value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}
