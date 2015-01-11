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

use Doctrine\Common\Cache\Cache;
use Ivory\HttpAdapter\FileGetContentsHttpAdapter;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Swap\Cache\DoctrineCache;
use Swap\Provider\ChainProvider;
use Swap\Provider\EuropeanCentralBankProvider;
use Swap\Provider\GoogleFinanceProvider;
use Swap\Provider\OpenExchangeRatesProvider;
use Swap\Provider\WebserviceXProvider;
use Swap\Provider\XigniteProvider;
use Swap\Provider\YahooFinanceProvider;

/**
 * Helps building Swap instances.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Builder
{
    private $httpAdapter;
    private $cache;
    private $providers = [];

    public function __construct(HttpAdapterInterface $httpAdapter = null)
    {
        $this->httpAdapter = $httpAdapter ?: new FileGetContentsHttpAdapter();
    }

    /**
     * Adds the ECB provider.
     *
     * @return Builder
     */
    public function europeanCentralBankProvider()
    {
        $this->providers[] = new EuropeanCentralBankProvider($this->httpAdapter);

        return $this;
    }

    /**
     * Adds the GoogleFinance provider.
     *
     * @return Builder
     */
    public function googleFinanceProvider()
    {
        $this->providers[] = new GoogleFinanceProvider($this->httpAdapter);

        return $this;
    }

    /**
     * Adds the OpenExchangeRayes provider.
     *
     * @param string $appId     The app id
     * @param bool  $enterprise Whether to use the enterprise mode
     *
     * @return Builder
     */
    public function openExchangeRatesProvider($appId, $enterprise = false)
    {
        $this->providers[] = new OpenExchangeRatesProvider($this->httpAdapter, $appId, $enterprise);

        return $this;
    }

    /**
     * Adds the WebserviceX provider.
     *
     * @return Builder
     */
    public function webserviceXProvider()
    {
        $this->providers[] = new WebserviceXProvider($this->httpAdapter);

        return $this;
    }

    /**
     * Adds the Xignite provider.
     *
     * @param string $token
     *
     * @return Builder
     */
    public function xigniteProvider($token)
    {
        $this->providers[] = new XigniteProvider($this->httpAdapter, $token);

        return $this;
    }

    /**
     * Adds the Yahoo Finance provider.
     *
     * @return Builder
     */
    public function yahooFinanceProvider()
    {
        $this->providers[] = new YahooFinanceProvider($this->httpAdapter);

        return $this;
    }

    /**
     * Adds Doctrine cache.
     *
     * @param Cache   $cache The cache to use
     * @param integer $ttl   The ttl in seconds
     *
     * @return Builder
     */
    public function doctrineCache(Cache $cache, $ttl = 0)
    {
        $this->cache = new DoctrineCache($cache, $ttl);

        return $this;
    }

    /**
     * Builds the Swap instance.
     *
     * @return Swap
     */
    public function build()
    {
        $countProviders = count($this->providers);

        if (0 === $countProviders) {
            throw new \RuntimeException('At least one provider must be added');
        }

        $provider = $countProviders > 1 ? new ChainProvider($this->providers) : $this->providers[0];

        return new Swap($provider, $this->cache);
    }
}
