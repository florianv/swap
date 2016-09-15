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

use Exchanger\Exchanger;
use Exchanger\Service\Chain;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Psr\Cache\CacheItemPoolInterface;
use Swap\Service\Factory;

/**
 * Helps building Swap.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Builder
{
    /**
     * The services.
     *
     * @var array
     */
    private $services = [];

    /**
     * The options.
     *
     * @var array
     */
    private $options = [];

    /**
     * The http client.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * The request factory.
     *
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * The cache item pool.
     *
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Adds a service.
     *
     * @param string $serviceName
     * @param array  $options
     *
     * @return Builder
     */
    public function add($serviceName, array $options = [])
    {
        $this->services[$serviceName] = $options;

        return $this;
    }

    /**
     * Uses the given http client.
     *
     * @param HttpClient $httpClient
     *
     * @return Builder
     */
    public function useHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Uses the given request factory.
     *
     * @param RequestFactory $requestFactory
     *
     * @return Builder
     */
    public function useRequestFactory(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    /**
     * Uses the given cache item pool.
     *
     * @param CacheItemPoolInterface $cacheItemPool
     *
     * @return Builder
     */
    public function useCacheItemPool(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;

        return $this;
    }

    /**
     * Builds Swap.
     *
     * @return Swap
     */
    public function build()
    {
        $serviceFactory = new Factory($this->httpClient, $this->requestFactory);
        $services = [];

        foreach ($this->services as $name => $options) {
            $services[] = $serviceFactory->create($name, $options);
        }

        $service = new Chain($services);
        $exchanger = new Exchanger($service, $this->cacheItemPool, $this->options);

        return new Swap($exchanger);
    }
}
