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

use Swap\ExchangeRate;
use Swap\ExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\ExchangeRateProvider;

class ExchangeRateProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedExchangeQueryException
     */
    public function it_throws_an_exception_when_provider_does_not_support_query()
    {
        $provider = $this->getMock('Swap\Contract\ExchangeRateService');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(false));

        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'));

        $swap = new ExchangeRateProvider($provider);
        $swap->getExchangeRate($ExchangeRateQuery);
    }

    /**
     * @test
     */
    public function it_quotes_a_pair()
    {
        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'));
        $provider = $this->getMock('Swap\Contract\ExchangeRateService');
        $rate = new ExchangeRate('1', new \DateTime());

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $provider
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($rate));

        $swap = new ExchangeRateProvider($provider);

        $this->assertSame($rate, $swap->getExchangeRate($ExchangeRateQuery));
    }

    /**
     * @test
     */
    public function it_quotes_an_identical_pair()
    {
        $provider = $this->getMock('Swap\Contract\ExchangeRateService');
        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/EUR'));

        $swap = new ExchangeRateProvider($provider);
        $rate = $swap->getExchangeRate($ExchangeRateQuery);

        $this->assertSame('1', $rate->getValue());
        $this->assertInstanceOf('\DateTime', $rate->getDate());
    }

    /**
     * @test
     */
    public function it_does_not_cache_identical_pairs()
    {
        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/EUR'));
        $provider = $this->getMock('Swap\Contract\ExchangeRateService');
        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->never())
            ->method('getItem');

        $swap = new ExchangeRateProvider($provider, $pool);
        $rate1 = $swap->getExchangeRate($ExchangeRateQuery);
        $rate2 = $swap->getExchangeRate($ExchangeRateQuery);

        $this->assertNotSame($rate1, $rate2, 'Identical pairs are not cached');
    }

    /**
     * @test
     */
    public function it_returns_null_if_rate_absent_in_cache()
    {
        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'));
        $pair = $ExchangeRateQuery->getCurrencyPair();

        $provider = $this->getMock('Swap\Contract\ExchangeRateService');

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

        $swap = new ExchangeRateProvider($provider, $pool);
        $this->assertNull($swap->getExchangeRate($ExchangeRateQuery));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_from_cache()
    {
        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'));
        $pair = $ExchangeRateQuery->getCurrencyPair();
        $rate = new ExchangeRate('1', new \DateTime());

        $provider = $this->getMock('Swap\Contract\ExchangeRateService');

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

        $swap = new ExchangeRateProvider($provider, $pool);
        $this->assertSame($rate, $swap->getExchangeRate($ExchangeRateQuery));
    }

    /**
     * @test
     */
    public function it_caches_a_rate()
    {
        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'));
        $pair = $ExchangeRateQuery->getCurrencyPair();
        $rate = new ExchangeRate('1', new \DateTime());
        $ttl = 3600;

        $provider = $this->getMock('Swap\Contract\ExchangeRateService');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $provider
            ->expects($this->once())
            ->method('get')
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

        $swap = new ExchangeRateProvider($provider, $pool, ['cache_ttl' => $ttl]);
        $swap->getExchangeRate($ExchangeRateQuery);
    }

    /**
     * @test
     */
    public function it_does_not_use_cache_if_refresh()
    {
        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), ['refresh' => true]);

        $pair = $ExchangeRateQuery->getCurrencyPair();

        $provider = $this->getMock('Swap\Contract\ExchangeRateService');

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

        $swap = new ExchangeRateProvider($provider, $pool);
        $swap->getExchangeRate($ExchangeRateQuery);
    }

    /**
     * @test
     */
    public function it_supports_overrding_ttl_per_query()
    {
        $ttl = 3600;
        $ExchangeRateQuery = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), ['cache_ttl' => $ttl]);
        $pair = $ExchangeRateQuery->getCurrencyPair();
        $rate = new ExchangeRate('1', new \DateTime());

        $provider = $this->getMock('Swap\Contract\ExchangeRateService');

        $provider
            ->expects($this->any())
            ->method('support')
            ->will($this->returnValue(true));

        $provider
            ->expects($this->once())
            ->method('get')
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

        $swap = new ExchangeRateProvider($provider, $pool);
        $swap->getExchangeRate($ExchangeRateQuery);
    }
}
