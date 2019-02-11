<?php

declare(strict_types=1);

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Service;

use Exchanger\Service\HttpService;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;
use Http\Message\RequestFactory;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Helps building services.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Factory
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
     * @param HttpClient|ClientInterface|null $httpClient
     * @param RequestFactoryInterface|null    $requestFactory
     */
    public function __construct($httpClient = null, RequestFactoryInterface $requestFactory = null)
    {
        if (null === $httpClient) {
            $httpClient = HttpClientDiscovery::find();
        } else {
            if (!$httpClient instanceof ClientInterface && !$httpClient instanceof HttpClient) {
                throw new \LogicException('Client must be an instance of Http\\Client\\HttpClient or Psr\\Http\\Client\\ClientInterface');
            }
        }

        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->registry = new Registry();
    }

    /**
     * Sets the http client.
     *
     * @param HttpClient|ClientInterface $httpClient
     */
    public function setHttpClient($httpClient): void
    {
        if (!$httpClient instanceof ClientInterface && !$httpClient instanceof HttpClient) {
            throw new \LogicException('Client must be an instance of Http\\Client\\HttpClient or Psr\\Http\\Client\\ClientInterface');
        }

        $this->httpClient = $httpClient;
    }

    /**
     * Sets the request factory.
     *
     * @param RequestFactoryInterface $requestFactory
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory): void
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
    public function create(string $serviceName, array $args = [])
    {
        if (!$this->registry->has($serviceName)) {
            throw new \LogicException(sprintf('The service "%s" is not registered.', $serviceName));
        }

        $classOrCallable = $this->registry->get($serviceName);

        if (is_callable($classOrCallable)) {
            return call_user_func($classOrCallable);
        }

        if (is_subclass_of($classOrCallable, HttpService::class)) {
            return new $classOrCallable($this->httpClient, $this->requestFactory, $args);
        }

        $r = new \ReflectionClass($classOrCallable);

        return $r->newInstanceArgs($args);
    }
}
