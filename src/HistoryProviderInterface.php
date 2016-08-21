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
 * Contract for providers retrieving historical data.
 *
 * @author Petr Kramar <petr.kramar@perlur.cz>
 */
interface HistoryProviderInterface extends ProviderInterface
{
    /**
     * Set date.
     *
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date);
}
