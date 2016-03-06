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

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Swap\ProviderInterface;

/**
 * Base class for providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var MessageFactory
     */
    private $httpMessageFactory;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient = null)
    {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
    }

    /**
     * Fetches the content of the given url.
     *
     * @param string $url
     *
     * @return string
     */
    protected function fetchContent($url)
    {
        $request = $this->getHttpMessageFactory()->createRequest('GET', $url);

        return $this->httpClient->sendRequest($request)->getBody()->__toString();
    }

    /**
     * @return MessageFactory
     */
    private function getHttpMessageFactory()
    {
        if ($this->httpMessageFactory === null) {
            $this->httpMessageFactory = MessageFactoryDiscovery::find();
        }

        return $this->httpMessageFactory;
    }

    /**
     * @param MessageFactory $httpMessageFactory
     *
     * @return AbstractProvider
     */
    public function setHttpMessageFactory($httpMessageFactory)
    {
        $this->httpMessageFactory = $httpMessageFactory;

        return $this;
    }
}
