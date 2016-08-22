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

use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Swap;

class SwapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_quotes_a_pair()
    {
        $provider = $this->getMock('Swap\ProviderInterface');
        $rate = new Rate('1', new \DateTime());

        $provider
            ->expects($this->once())
            ->method('fetchRate')
            ->will($this->returnValue($rate));

        $swap = new Swap($provider);

        $this->assertSame($rate, $swap->quote('EUR/USD'));
    }

    /**
     * @test
     */
    public function it_quotes_an_identical_pair()
    {
        $provider = $this->getMock('Swap\ProviderInterface');
        $pair = new CurrencyPair('EUR', 'EUR');

        $swap = new Swap($provider);
        $rate = $swap->quote($pair);

        $this->assertSame('1', $rate->getValue());
        $this->assertInstanceOf('\DateTime', $rate->getDate());
    }

    /**
     * @test
     */
    public function it_does_not_cache_identical_pairs()
    {
        $pair = new CurrencyPair('EUR', 'EUR');
        $provider = $this->getMock('Swap\ProviderInterface');
        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->never())
            ->method('getItem');

        $swap = new Swap($provider, $pool);
        $rate1 = $swap->quote($pair);
        $rate2 = $swap->quote($pair);

        $this->assertNotSame($rate1, $rate2, 'Identical pairs are not cached');
    }

    /**
     * @test
     */
    public function it_returns_null_if_rate_absent_in_cache()
    {
        $pair = new CurrencyPair('EUR', 'USD');

        $provider = $this->getMock('Swap\ProviderInterface');

        $item = $this->getMock('Psr\Cache\CacheItemInterface');

        $item
            ->expects($this->once())
            ->method('isHit')
            ->will($this->returnValue(false));

        $pool = $this->getMock('Psr\Cache\CacheItemPoolInterface');

        $pool
            ->expects($this->once())
            ->method('getItem')
            ->with($pair->toString())
            ->will($this->returnValue($item));

        $swap = new Swap($provider, $pool);
        $this->assertNull($swap->quote($pair));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate_from_cache()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $rate = new Rate('1', new \DateTime());

        $provider = $this->getMock('Swap\ProviderInterface');

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
            ->with($pair->toString())
            ->will($this->returnValue($item));

        $swap = new Swap($provider, $pool);
        $this->assertSame($rate, $swap->quote($pair));
    }

    /**
     * @test
     */
    public function it_caches_a_rate()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $rate = new Rate('1', new \DateTime());
        $ttl = 3600;

        $provider = $this->getMock('Swap\ProviderInterface');

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
            ->with($pair->toString())
            ->will($this->returnValue($item));

        $pool
            ->expects($this->once())
            ->method('save')
            ->with($item);

        $swap = new Swap($provider, $pool, $ttl);
        $swap->quote($pair);
    }
}
