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

use Swap\Cache\DoctrineCache;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;

class DoctrineCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_null_if_rate_absent()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $cache = $this->getMock('Doctrine\Common\Cache\Cache');

        $cache
            ->expects($this->once())
            ->method('fetch')
            ->with($pair)
            ->will($this->returnValue(false))
        ;

        $doctrineCache = new DoctrineCache($cache);
        $this->assertNull($doctrineCache->fetchRate($pair));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $rate = new Rate('1', new \DateTime());
        $cache = $this->getMock('Doctrine\Common\Cache\Cache');

        $cache
            ->expects($this->once())
            ->method('fetch')
            ->with($pair)
            ->will($this->returnValue($rate))
        ;

        $doctrineCache = new DoctrineCache($cache);
        $this->assertSame($rate, $doctrineCache->fetchRate($pair));
    }

    /**
     * @test
     */
    public function it_stores_a_rate()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $rate = new Rate('1', new \DateTime());
        $cache = $this->getMock('Doctrine\Common\Cache\Cache');

        $cache
            ->expects($this->once())
            ->method('save')
            ->with('EUR/USD', $rate, 3600)
            ->will($this->returnValue($rate))
        ;

        $doctrineCache = new DoctrineCache($cache, 3600);
        $doctrineCache->storeRate($pair, $rate);
    }
}
