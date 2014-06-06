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

use GuzzleHttp\Message\ResponseInterface;
use Swap\Exception\AdapterException;
use GuzzleHttp\ClientInterface;
use Swap\AdapterInterface;

/**
 * Adapter for Guzzle4.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Guzzle4Adapter implements AdapterInterface
{
    private $client;

    /**
     * Creates a new adapter.
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
        try {
            $response = $this->client->get($uri);
        } catch (\Exception $e) {
            throw new AdapterException($e->getMessage());
        }

        return (string) $response->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(array $uris)
    {
        $requests = array();
        foreach ($uris as $uri) {
            $requests[] = $this->client->createRequest('GET', $uri);
        }

        try {
            /** @var \GuzzleHttp\Message\Response[] $responses */
            $results = \GuzzleHttp\batch($this->client, $requests);
        } catch (\Exception $e) {
            throw new AdapterException($e->getMessage());
        }

        $bodies = array();
        foreach ($results as $request) {
            /** @var \GuzzleHttp\Exception\RequestException|ResponseInterface $result */
            $result = $results[$request];

            if ($result instanceof ResponseInterface) {
                $bodies[] = (string) $result->getBody();
            } else {
                throw new AdapterException($result->getMessage());
            }
        }

        return $bodies;
    }
}
