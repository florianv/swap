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

use Doctrine\Common\Cache\ArrayCache;
use Swap\Cache\DoctrineCache;
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
    public function it_caches_a_rate()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $provider = $this->getMock('Swap\ProviderInterface');
        $cache = new DoctrineCache(new ArrayCache(), 3600);

        $provider
            ->expects($this->once())
            ->method('fetchRate')
            ->with($pair)
            ->will($this->returnValue(new Rate('1', new \DateTime())));

        $swap = new Swap($provider, $cache);
        $rate1 = $swap->quote($pair);
        $rate2 = $swap->quote($pair);
        $rate3 = $swap->quote('EUR/USD');

        $this->assertSame($rate1, $rate2);
        $this->assertSame($rate2, $rate3);
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
}
