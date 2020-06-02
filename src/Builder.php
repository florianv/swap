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

namespace Swap;

use Exchanger\Contract\ExchangeRateService;
use Exchanger\Exchanger;
use Exchanger\Service\Chain;
use Http\Client\HttpClient;
use Psr\SimpleCache\CacheInterface;
use Swap\Service\Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Helps building Swap.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Builder
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
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * The cache.
     *
     * @var CacheInterface
     */
    private $cache;

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
     * Adds a service by service name.
     *
     * @param string $serviceName
     * @param array  $options
     *
     * @return Builder
     */
    public function add(string $serviceName, array $options = []): self
    {
        $this->services[$serviceName] = $options;

        return $this;
    }

    /**
     * Add a service by service instance.
     *
     * @param ExchangeRateService $exchangeRateService
     *
     * @return Builder
     */
    public function addExchangeRateService(ExchangeRateService $exchangeRateService): self
    {
        $this->services[spl_object_hash($exchangeRateService)] = $exchangeRateService;

        return $this;
    }

    /**
     * Uses the given http client.
     *
     * @param HttpClient|ClientInterface $httpClient
     *
     * @return Builder
     */
    public function useHttpClient($httpClient): self
    {
        if (!$httpClient instanceof ClientInterface && !$httpClient instanceof HttpClient) {
            throw new \LogicException('Client must be an instance of Http\\Client\\HttpClient or Psr\\Http\\Client\\ClientInterface');
        }

        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Uses the given request factory.
     *
     * @param RequestFactoryInterface $requestFactory
     *
     * @return Builder
     */
    public function useRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    /**
     * Uses the given simple cache.
     *
     * @param CacheInterface $cache
     *
     * @return Builder
     */
    public function useSimpleCache(CacheInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Builds Swap.
     *
     * @return Swap
     */
    public function build(): Swap
    {
        $serviceFactory = new Factory($this->httpClient, $this->requestFactory);

        /** @var ExchangeRateService[] $services */
        $services = [];

        foreach ($this->services as $name => $optionsOrService) {
            /** @var array|ExchangeRateService $optionsOrService */
            if ($optionsOrService instanceof ExchangeRateService) {
                $services[] = $optionsOrService;
            } else {
                $services[] = $serviceFactory->create($name, $optionsOrService);
            }
        }

        $service = new Chain($services);
        $exchanger = new Exchanger($service, $this->cache, $this->options);

        return new Swap($exchanger);
    }
}
