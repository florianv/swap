<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Provider;

use Guzzle\Http\ClientInterface;
use Swap\ProviderInterface;

/**
 * Base class for providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * The HTTP client.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Creates a new abstract provider.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }
}
