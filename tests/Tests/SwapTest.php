<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests;

use Swap\ExchangeQuery;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Swap;

class SwapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedExchangeQueryException
     * @expectedExceptionMessage The exchange query "EUR/USD" is not supported by the provider.
     */
    public function it_throws_an_exception_when_provider_does_not_support_query()
    {
        $provider = $this->getMock('Swap\ProviderInterface');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(false));

        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/USD'));

        $swap = new Swap($provider);
        $swap->getExchangeRate($exchangeQuery);
    }

    /**
     * @test
     */
    public function it_quotes_a_pair()
    {
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/USD'));
        $provider = $this->getMock('Swap\ProviderInterface');
        $rate = new Rate('1', new \DateTime());

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $provider
            ->expects($this->once())
            ->method('fetchRate')
            ->will($this->returnValue($rate));

        $swap = new Swap($provider);

        $this->assertSame($rate, $swap->getExchangeRate($exchangeQuery));
    }

    /**
     * @test
     */
    public function it_quotes_an_identical_pair()
    {
        $provider = $this->getMock('Swap\ProviderInterface');
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/EUR'));

        $swap = new Swap($provider);
        $rate = $swap->getExchangeRate($exchangeQuery);

        $this->assertSame('1', $rate->getValue());
        $this->assertInstanceOf('\DateTime', $rate->getDate());
    }

    /**
     * @test
     */
    public function it_does_not_cache_identical_pairs()
    {
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/EUR'));
        $provider = $this->getMock('Swap\ProviderInterface');
        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->never())
            ->method('getItem');

        $swap = new Swap($provider, $pool);
        $rate1 = $swap->getExchangeRate($exchangeQuery);
        $rate2 = $swap->getExchangeRate($exchangeQuery);

        $this->assertNotSame($rate1, $rate2, 'Identical pairs are not cached');
    }

    /**
     * @test
     */
    public function it_returns_null_if_rate_absent_in_cache()
    {
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/USD'));
        $pair = $exchangeQuery->getCurrencyPair();

        $provider = $this->getMock('Swap\ProviderInterface');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $item = $this->getMock('Psr\Cache\CacheItemInterface');

        $item
            ->expects($this->once())
            ->method('isHit')
            ->will($this->returnValue(false));

        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->once())
            ->method('getItem')
            ->with($pair->toHash())
            ->will($this->returnValue($item));

        $swap = new Swap($provider, $pool);
        $this->assertNull($swap->getExchangeRate($exchangeQuery));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_from_cache()
    {
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/USD'));
        $pair = $exchangeQuery->getCurrencyPair();
        $rate = new Rate('1', new \DateTime());

        $provider = $this->getMock('Swap\ProviderInterface');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $item = $this->getMock('Psr\Cache\CacheItemInterface');

        $item
            ->expects($this->once())
            ->method('isHit')
            ->will($this->returnValue(true));

        $item
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($rate));

        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->once())
            ->method('getItem')
            ->with($pair->toHash())
            ->will($this->returnValue($item));

        $swap = new Swap($provider, $pool);
        $this->assertSame($rate, $swap->getExchangeRate($exchangeQuery));
    }

    /**
     * @test
     */
    public function it_caches_a_rate()
    {
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/USD'));
        $pair = $exchangeQuery->getCurrencyPair();
        $rate = new Rate('1', new \DateTime());
        $ttl = 3600;

        $provider = $this->getMock('Swap\ProviderInterface');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $provider
            ->expects($this->once())
            ->method('fetchRate')
            ->will($this->returnValue($rate));

        $item = $this->getMock('Psr\Cache\CacheItemInterface');

        $item
            ->expects($this->once())
            ->method('isHit')
            ->will($this->returnValue(false));

        $item
            ->expects($this->once())
            ->method('set')
            ->with($rate);

        $item
            ->expects($this->once())
            ->method('expiresAfter')
            ->with($ttl);

        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->once())
            ->method('getItem')
            ->with($pair->toHash())
            ->will($this->returnValue($item));

        $pool
            ->expects($this->once())
            ->method('save')
            ->with($item);

        $swap = new Swap($provider, $pool, ['cache_ttl' => $ttl]);
        $swap->getExchangeRate($exchangeQuery);
    }

    /**
     * @test
     */
    public function it_does_not_use_cache_if_refresh()
    {
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/USD'), ['refresh' => true]);

        $pair = $exchangeQuery->getCurrencyPair();

        $provider = $this->getMock('Swap\ProviderInterface');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $item = $this->getMock('Psr\Cache\CacheItemInterface');

        $item
            ->expects($this->never())
            ->method('get');

        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->once())
            ->method('getItem')
            ->with($pair->toHash())
            ->will($this->returnValue($item));

        $swap = new Swap($provider, $pool);
        $swap->getExchangeRate($exchangeQuery);
    }

    /**
     * @test
     */
    public function it_does_not_use_cache_if_disabled()
    {
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/USD'), ['cache_disabled' => true]);

        $provider = $this->getMock('Swap\ProviderInterface');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->never())
            ->method('getItem');

        $swap = new Swap($provider, $pool);
        $swap->getExchangeRate($exchangeQuery);
    }

    /**
     * @test
     */
    public function it_supports_overrding_ttl_per_query()
    {
        $ttl = 3600;
        $exchangeQuery = new ExchangeQuery(CurrencyPair::createFromString('EUR/USD'), ['cache_ttl' => $ttl]);
        $pair = $exchangeQuery->getCurrencyPair();
        $rate = new Rate('1', new \DateTime());

        $provider = $this->getMock('Swap\ProviderInterface');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $provider
            ->expects($this->once())
            ->method('fetchRate')
            ->will($this->returnValue($rate));

        $item = $this->getMock('Psr\Cache\CacheItemInterface');

        $item
            ->expects($this->once())
            ->method('isHit')
            ->will($this->returnValue(false));

        $item
            ->expects($this->once())
            ->method('set')
            ->with($rate);

        $item
            ->expects($this->once())
            ->method('expiresAfter')
            ->with($ttl);

        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->once())
            ->method('getItem')
            ->with($pair->toHash())
            ->will($this->returnValue($item));

        $pool
            ->expects($this->once())
            ->method('save')
            ->with($item);

        $swap = new Swap($provider, $pool);
        $swap->getExchangeRate($exchangeQuery);
    }
}
