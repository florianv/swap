<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Cache;

use Swap\Cache\IlluminateCache;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;

class IlluminateCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_null_if_rate_absent()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $store = $this->getMock('Illuminate\Contracts\Cache\Store');

        $store
            ->expects($this->once())
            ->method('get')
            ->with($pair)
            ->will($this->returnValue(null))
        ;

        $cache = new IlluminateCache($store);
        $this->assertNull($cache->fetchRate($pair));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $rate = new Rate('1', new \DateTime());
        $store = $this->getMock('Illuminate\Contracts\Cache\Store');

        $store
            ->expects($this->once())
            ->method('get')
            ->with($pair)
            ->will($this->returnValue($rate))
        ;

        $cache = new IlluminateCache($store);
        $this->assertSame($rate, $cache->fetchRate($pair));
    }

    /**
     * @test
     */
    public function it_stores_a_rate()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $rate = new Rate('1', new \DateTime());
        $store = $this->getMock('Illuminate\Contracts\Cache\Store');

        $store
            ->expects($this->once())
            ->method('put')
            ->with('EUR/USD', $rate, 60)
        ;

        $cache = new IlluminateCache($store, 60);
        $cache->storeRate($pair, $rate);
    }
}
