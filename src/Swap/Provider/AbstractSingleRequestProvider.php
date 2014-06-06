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

/**
 * Base class for providers sending a single request to quote multiple pairs.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractSingleRequestProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function quote(array $pairs)
    {
        $uri = $this->prepareRequestUri($pairs);

        $response = $this->client->get($uri);

        $this->processResponse($response, $pairs);
    }

    /**
     * Prepares the request uri.
     *
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     *
     * @return string
     */
    abstract protected function prepareRequestUri(array $pairs);

    /**
     * Processes the response.
     *
     * @param string                              $body
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     */
    abstract protected function processResponse($body, array $pairs);
}
