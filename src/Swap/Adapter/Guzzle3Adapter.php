<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Adapter;

use Swap\Exception\AdapterException;
use Guzzle\Http\ClientInterface;
use Swap\AdapterInterface;

/**
 * Adapter for Guzzle3.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Guzzle3Adapter implements AdapterInterface
{
    private $client;

    /**
     * Creates a new client.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri)
    {
        $request = $this->client->get($uri);

        try {
            $response = $this->client->send($request);
        } catch (\Exception $e) {
            throw new AdapterException($e->getMessage());
        }

        return $response->getBody(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(array $uris)
    {
        $requests = array();
        foreach ($uris as $uri) {
            $requests[] = $this->client->get($uri);
        }

        try {
            /** @var \Guzzle\Http\Message\Response[] $responses */
            $responses = $this->client->send($requests);
        } catch (\Exception $e) {
            throw new AdapterException($e->getMessage());
        }

        $bodies = array();
        foreach ($responses as $response) {
            $bodies[] = $response->getBody(true);
        }

        return $bodies;
    }
}
