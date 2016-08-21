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

use Swap\Model\QuotationRequest;
use Swap\Model\Rate;

/**
 * Contract for caches.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface CacheInterface
{
    /**
     * Fetches the rate.
     *
     * @param QuotationRequest $request
     *
     * @return Rate|null
     */
    public function fetchRate(QuotationRequest $request);

    /**
     * Stores the rate.
     *
     * @param QuotationRequest $request
     * @param Rate             $rate
     */
    public function storeRate(QuotationRequest $request, Rate $rate);
}
