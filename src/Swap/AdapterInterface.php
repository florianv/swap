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
 * Contract for clients adapters.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface AdapterInterface
{
    /**
     * Performs a GET request and returns the response body.
     *
     * @param string $uri
     *
     * @return string
     *
     * @throws Exception\AdapterException
     */
    public function get($uri);

    /**
     * Performs multiple GET requests and returns the responses bodies.
     *
     * @param array $uris
     *
     * @return array
     *
     * @throws Exception\AdapterException
     */
    public function getAll(array $uris);
}
