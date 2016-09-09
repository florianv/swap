<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Service;

use Exchanger\Service\Service;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;

/**
 * Helps building services.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Factory
{
    /**
     * The client.
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
     * The service registry.
     *
     * @var Registry
     */
    private $registry;

    /**
     * @param HttpClient|null     $httpClient
     * @param RequestFactory|null $requestFactory
     */
    public function __construct(HttpClient $httpClient = null, RequestFactory $requestFactory = null)
    {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: MessageFactoryDiscovery::find();
        $this->registry = new Registry();
    }

    /**
     * Sets the http client.
     *
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Sets the request factory.
     *
     * @param RequestFactory $requestFactory
     */
    public function setRequestFactory(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * Creates a new service.
     *
     * @param string $serviceName
     * @param array  $args
     *
     * @return \Exchanger\Contract\ExchangeRateService
     */
    public function create($serviceName, array $args = [])
    {
        if (!$this->registry->has($serviceName)) {
            throw new \InvalidArgumentException(sprintf('The service "%s" is not registered.', $serviceName));
        }

        $classOrCallable = $this->registry->get($serviceName);

        if (is_callable($classOrCallable)) {
            return call_user_func($classOrCallable);
        }

        if (is_subclass_of($classOrCallable, Service::class)) {
            return new $classOrCallable($this->httpClient, $this->requestFactory, $args);
        }

        $r = new \ReflectionClass($classOrCallable);

        return $r->newInstanceArgs($args);
    }
}
