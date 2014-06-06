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
 * Base class for providers sending multiple requests to quote multiple pairs.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractMultiRequestsProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function quote(array $pairs)
    {
        $uris = $this->prepareUris($pairs);

        $responses = $this->client->getAll($uris);

        $this->processResponses($responses, $pairs);
    }

    /**
     * Prepares the requests URIs from the pairs to quote.
     *
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     *
     * @return array
     */
    abstract protected function prepareUris(array $pairs);

    /**
     * Processes the responses.
     *
     * @param array                               $bodies
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     */
    abstract protected function processResponses(array $bodies, array $pairs);
}
