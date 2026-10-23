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

use Exchanger\Contract\ExchangeRateService;
use Exchanger\Service\HttpService;
use Http\Client\HttpClient;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Helps building services.
 *
 * @api
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Factory
{
    /**
     * The client.
     *
     * @psalm-suppress DeprecatedInterface HTTPlug clients stay accepted for BC until 5.0.
     *
     * @var HttpClient|ClientInterface
     */
    private $httpClient;

    /**
     * The request factory.
     *
     * @var RequestFactoryInterface
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
    public function __construct($httpClient = null, ?RequestFactoryInterface $requestFactory = null)
    {
        if (null === $httpClient) {
            $httpClient = Psr18ClientDiscovery::find();
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
     * @return ExchangeRateService
     */
    public function create(string $serviceName, array $args = [])
    {
        $classOrCallable = $this->registry->get($serviceName);

        if (null === $classOrCallable) {
            throw new \LogicException(sprintf('The service "%s" is not registered.', $serviceName));
        }

        if (is_callable($classOrCallable)) {
            /** @var ExchangeRateService Callables registered for a service must return it. */
            return call_user_func($classOrCallable);
        }

        if (is_subclass_of($classOrCallable, HttpService::class)) {
            /** @psalm-suppress UnsafeInstantiation HttpService subclasses keep its constructor signature. */
            return new $classOrCallable($this->httpClient, $this->requestFactory, $args);
        }

        $r = new \ReflectionClass($classOrCallable);

        return $r->newInstanceArgs($args);
    }
}
