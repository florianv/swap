<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Provider;

use Guzzle\Http\ClientInterface;
use Swap\Adapter\Guzzle3Adapter;
use Swap\ProviderInterface;
use Swap\AdapterInterface;

/**
 * Base class for providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractProvider implements ProviderInterface
{
    protected $client;

    /**
     * Creates a new abstract provider.
     *
     * @param ClientInterface|AdapterInterface $client
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($client)
    {
        // For BC with version 1.0
        if ($client instanceof ClientInterface) {
            $client = new Guzzle3Adapter($client);
        } elseif (!$client instanceof AdapterInterface) {
            throw new \InvalidArgumentException('The client must implement "Swap\AdapterInterface".');
        }

        $this->client = $client;
    }
}
